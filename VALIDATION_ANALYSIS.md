# Laravel Agenda Project - Validation Implementation Analysis

**Date:** April 7, 2026  
**Project:** AgendaArpus  
**Framework:** Laravel + Filament

---

## Executive Summary

This is a **moderate-scale Laravel project** with a **hybrid validation approach**. The project uses:
1. **Form Request classes** for API validation
2. **Filament form schemas** with inline validation rules
3. **Service layer** with centralized validation logic
4. **Model observers** for cache invalidation (not validation)
5. **Policies** for authorization (not validation)

**Overall Assessment:** The validation implementation is **distributed and inconsistent**. There are clear gaps in validation coverage across different endpoints.

---

## 1. Current Validation Implementation

### 1.1 Form Request Classes

**Location:** `app/Http/Requests/`

#### **KalenderFeedRequest.php**
- **Purpose:** Validates GET parameters for calendar feed API
- **Validation Rules:**
  ```php
  'start' => ['required', 'date']
  'end' => ['required', 'date', 'after_or_equal:start']
  ```
- **Custom Messages:** Yes, Indonesian locale messages provided
- **Additional Features:** 
  - `parsed()` method for date parsing (Carbon conversion)
  - Custom validation messages in Indonesian
  - `withValidator()` method (incomplete in current code)

**Issues Found:**
- ❌ No date format specification (even though messages mention YYYY-MM-DD format)
- ❌ No maximum date range validation (user could request 10 years of data)
- ❌ No input sanitization
- ⚠️ Incomplete `withValidator()` implementation

---

### 1.2 Filament Form Schemas

**Location:** `app/Filament/Resources/`

#### **AgendaForm.php** (for Agenda resource)
**Validation Rules:**
- `judul_agenda`: `required`, `maxLength(100)`, `unique` (via live validation)
- `slug`: `hidden`, `unique(ignoreRecord: true)`, `dehydrated(false)`, `disabled`
- `deskripsi`: `maxLength(150)`
- `location`: `required`, `maxLength(100)`
- `start_date`: `required`, timezone-aware (`Asia/Jakarta`)
- `end_date`: optional, timezone-aware (`Asia/Jakarta`)
- `bidang_id`: `multiple` select relationship (no explicit required rule shown)
- `is_published`: `Toggle`, default `false`

**Features:**
- ✅ Live validation with `live(onBlur: true)`
- ✅ Slug auto-generation from title
- ✅ Timezone specification (Asia/Jakarta)
- ✅ Helpful placeholder text

**Issues Found:**
- ❌ No date relationship validation (end_date should be >= start_date)
- ❌ No minimum length for `judul_agenda`
- ❌ No location validation (type/format)
- ❌ No regex pattern for title validation
- ❌ `bidang_id` relationship validation is implicit/missing
- ⚠️ Slug validation only on unique, no format validation

---

#### **BidangForm.php** (for Bidang resource)
**Validation Rules:**
- `nama_bidang`: `required`, `minLength(3)`, `maxLength(30)`, `unique` (via live validation)
- `slug`: `required`, `maxLength(25)`, auto-generated

**Features:**
- ✅ Live slug generation
- ✅ Min/max length specifications

**Issues Found:**
- ❌ No regex/format validation for nama_bidang despite special characters being handled
- ❌ No alpha_dash constraint for slug in Filament schema (exists only in service)
- ❌ No custom validation messages

---

### 1.3 Service Layer Validation

**Location:** `app/Services/`

#### **BaseService.php** (abstract base class)
**Approach:** Centralized validation logic using Laravel `Validator`

**Methods:**
- `getValidationRules(?int $id = null): array` - Abstract, must be implemented
- `getValidationMessages(): array` - Standard Indonesian messages
- `getValidationAttributes(): array` - Human-readable field names
- `validate(array $data, ?int $id = null): array` - Throws `ValidationException` on failure
- `sanitize(array $data): array` - Data sanitization (empty implementation in base)
- `create(array $data)` - Validate → Sanitize → Create
- `update(int $id, array $data)` - Validate → Sanitize → Update

**Default Messages:**
```php
'required' => ':attribute wajib diisi.'
'unique' => ':attribute ini sudah digunakan.'
'min' => ':attribute minimal :min karakter.'
'max' => ':attribute maksimal :max karakter.'
'regex' => 'Format :attribute tidak valid.'
'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, dash (-) dan underscore (_)'
```

---

#### **BidangService.php** (extends BaseService)
**Validation Rules:**
```php
'nama_bidang' => [
    'required',
    'string',
    'min:3',
    'max:255',
    'regex:/^[a-zA-Z\s\&\-\.\,]+$/',  // Only letters, spaces, &, -, ., ,
    'unique:bidang,nama_bidang,' . $id,
]
'slug' => [
    'required',
    'string',
    'max:255',
    'alpha_dash',
    'unique:bidang,slug,' . $id,
]
```

**Sanitization Methods:**
- Trim whitespace
- Normalize multi-spaces to single space
- Convert to title case (ucwords)
- Strip HTML tags
- Auto-generate slug from nama_bidang if empty
- Slug normalization via `Str::slug()`

**Custom Attributes:**
```php
'nama_bidang' => 'Nama Bidang'
'slug' => 'slug'
```

**Additional Methods:**
- `findBySlug(string $slug)` - Query by slug
- `search(string $keyword)` - Search functionality

---

### 1.4 Model-Level Implementation

**Location:** `app/Models/`

#### **Agenda Model**
**Validation:** ❌ None
**Features:**
- Auto-slug generation in `booted()` lifecycle hooks
- Query scopes for filtering (published, hariIni, mendatang, mingguIni, betweenDates)
- Activity logging via `LogsActivity` trait
- Fillable fields defined
- Proper datetime casting

**Gaps:**
- ❌ No rules property
- ❌ No validation logic
- ❌ No mutators for validation data

---

#### **Bidang Model**
**Validation:** ❌ None (relies on service layer)
**Features:**
- Auto-slug generation in `boot()` lifecycle hooks
- Soft deletes
- Custom accessors/mutators (getNamaBidangAttribute, setNamaBidangAttribute)
- Search scope

**Gaps:**
- ❌ Duplicated validation logic with service layer
- ⚠️ Mutator logic conflicts with service sanitization

---

#### **User Model**
**Validation:** ❌ None
**Authorization:** Uses Filament panel checks (not validation)
**Features:**
- Activity logging
- Email verification checks
- Domain-based access control (@dev.dev)

---

### 1.5 Controller Implementation

**Location:** `app/Http/Controllers/`

#### **AgendaController.php**
**Methods:**
- `index()` - Returns view with published agenda
- `polling()` - Returns JSON for polling updates
- `kalenderFeed(KalenderFeedRequest $request)` - Validates with FormRequest, returns calendar events
- `show(int $id)` - Basic ID validation only

**Validation Approach:**
- ✅ Uses FormRequest for `kalenderFeed()`
- ❌ Basic manual validation in `show()` method (only checks `$id <= 0`)
- ❌ No validation for `index()` or `polling()` (no parameters)

---

### 1.6 Filament Resource Classes

**Location:** `app/Filament/Resources/`

#### **AgendaResource & BidangResource**
**Validation Integration:**
- Uses form schemas (AgendaForm, BidangForm) for UI validation
- Implements data mutations:
  - `mutateFormDataBeforeCreate()` - Calls service sanitize
  - `mutateFormDataBeforeSave()` - Calls service sanitize

**Authorization:**
- Uses policy classes (AgendaPolicy, BidangPolicy)
- Policy methods check for Spatie permissions

**Issues:**
- ❌ **Critical:** No server-side re-validation after Filament form submission
- ❌ Filament validation rules are only UI-level (JavaScript)
- ⚠️ Client-side validation can be bypassed

---

---

## 2. Validation Rules by Entity

### **Agenda Table**
| Field | DB Type | Validation Rules | Status |
|-------|---------|------------------|--------|
| judul_agenda | string | required, maxLength(100), unique (implicit) | ⚠️ Partial |
| slug | string | unique, auto-generated | ✅ Good |
| deskripsi | text | maxLength(150) | ⚠️ Minimal |
| start_date | datetime | required, datetime | ✅ Good |
| end_date | datetime | optional, should validate >= start_date | ❌ Missing |
| location | string | required, maxLength(100) | ⚠️ Minimal |
| is_published | boolean | default false | ✅ Good |

### **Bidang Table**
| Field | DB Type | Validation Rules | Status |
|-------|---------|------------------|--------|
| nama_bidang | string | required, min:3, max:255, regex, unique | ✅ Good |
| slug | string | required, max:255, alpha_dash, unique | ✅ Good |

---

## 3. Endpoints Analysis

### Public API Endpoints
#### **GET /api/agenda/kalender** (with KalenderFeedRequest)
- ✅ **Validates:** start, end dates
- ⚠️ **Issues:** No date range limits
- **Response:** JSON calendar events

#### **GET /api/agenda/polling**
- ❌ **No validation** on request
- Returns JSON stats and HTML partials

#### **GET /api/agenda/{id}**
- ⚠️ **Minimal validation:** Only `$id <= 0` check in controller
- Should use FormRequest for consistency

#### **GET /** (Landing Page)
- ❌ **No validation** (doesn't need it - no parameters)

### Admin Panel Endpoints (Filament)
- **CREATE Agenda** - UI validation only (no server-side)
- **UPDATE Agenda** - UI validation only (no server-side)
- **DELETE Agenda** - Policy-based authorization only
- **CREATE Bidang** - UI validation only (no server-side)
- **UPDATE Bidang** - UI validation only (no server-side)
- **DELETE Bidang** - Policy-based authorization only

---

## 4. Issues and Gaps Found

### 🔴 Critical Issues

1. **No Server-Side Validation on Filament Forms**
   - Filament validation is purely UI-level (JavaScript/HTML5)
   - Can be completely bypassed by direct database manipulation or API calls
   - Recommendation: Implement FormRequest classes or middleware validation

2. **Date Relationship Validation Missing**
   - Agenda `end_date` should be >= `start_date`
   - Currently only validated in UI, not at database level
   - Could create invalid data via direct API or SQL

3. **No API Endpoint for Create/Update Agenda**
   - Only Filament admin panel can create/update agenda
   - If external systems need to integrate, they have no validated endpoint

4. **Inconsistent Validation Approach**
   - Filament resources use form schemas
   - APIs use FormRequest
   - Service layer has separate rules
   - This creates maintenance burden and potential inconsistencies

### 🟠 High Priority Issues

5. **No Date Range Limits on Calendar API**
   - User can request years of dates in single query
   - Could cause performance issues
   - No throttling beyond generic rate limiting

6. **Manual Validation in Controllers**
   - `show()` method has basic `$id <= 0` check
   - Should use `findOrFail()` or validation in FormRequest
   - Inconsistent error handling

7. **Bidang Validation Duplicated**
   - Rules defined in BidangService
   - Rules also defined in BidangForm schema
   - Changes to one won't automatically update the other

8. **No Input Sanitization in Agenda Model**
   - BidangService sanitizes input (HTML strip, trim, etc.)
   - Agenda has no equivalent sanitization
   - XSS vulnerability potential in deskripsi field

9. **Incomplete FormRequest Implementation**
   - `KalenderFeedRequest::withValidator()` method is incomplete
   - Unclear what validation it was meant to perform

### 🟡 Medium Priority Issues

10. **Missing Validation Rules**
    - `judul_agenda`: No minimum length, no format validation
    - `location`: No format validation or special character handling
    - `deskripsi`: No minimum length
    - `bidang_id`: No explicit required validation in schema

11. **No Unique Validation on API**
    - `judul_agenda` should likely be unique (implied in UI but not enforced)
    - Could have duplicate agenda titles via API

12. **DateTime Format Not Specified**
    - API assumes ISO-8601 format (Laravel default)
    - No explicit format validation
    - Filament uses DateTimePicker (user-friendly)
    - Could cause issues with external api consumers

13. **Slug Generation Not Validated**
    - Slug is hidden and auto-generated in Filament
    - But not validated for uniqueness at service layer on Agenda
    - Only Bidang has full slug validation

14. **Status/is_published Field**
    - No authorization check to prevent unpublished agenda display
    - Anyone seeing the `is_published` flag could change it if validation bypassed

### 🟢 Minor Issues

15. **English/Indonesian Language Mixing**
    - Some validation messages in Indonesian
    - Field names in English
    - Inconsistent user experience

16. **No Soft Deletes on Agenda**
    - Bidang has soft deletes, Agenda doesn't
    - Inconsistent data retention policy

17. **Limited Error Messages**
    - Date format messages mention YYYY-MM-DD but validation uses generic 'date' rule
    - Could confuse API consumers

---

## 5. Validation Flow Diagram

### Current Flow for Bidang (via Filament)
```
User Input (Filament UI)
    ↓
HTML5 Validation (Browser) ← [Can be bypassed]
    ↓
Filament JavaScript Validation ← [Can be bypassed]
    ↓
Filament Form Submission (HTTP POST)
    ↓
BidangResource::mutateFormDataBeforeCreate()
    ↓
BidangService::sanitize() ← [No validation here!]
    ↓
Model::create() ← [No validation at model level]
    ↓
Database Insert
```

### Current Flow for Agenda API (via Laravel)
```
API Request (GET /api/agenda/kalender?start=X&end=Y)
    ↓
KalenderFeedRequest Validation ✓
    ↓
Controller Action
    ↓
Query & Return Response
```

---

## 6. Best Practices Not Implemented

- ❌ No request object pattern for Filament forms (using form schemas instead)
- ❌ No consistent validation layer across all endpoints
- ❌ No input sanitization on Agenda model
- ❌ No validation tests/integration tests visible
- ❌ No custom rule classes for complex validation logic
- ⚠️ Limited use of mutators in models
- ⚠️ No middleware for global validation concerns

---

## 7. Security Concerns

1. **XSS Risk**
   - `deskripsi` and `location` fields not sanitized
   - Displayed in views without visible escaping rules

2. **Date Range DoS**
   - No limits on calendar date range queries
   - Could be exploited for performance degradation

3. **Authorization vs Authentication**
   - Policies check permissions (authorization) not data validation
   - Could bypass validation checks if permission checks fail unexpectedly

4. **Filament Bypass**
   - No server-side validation on form submission
   - Attacker could send raw POST requests to Filament endpoints

5. **Type Safety**
   - No type hints in BaseService
   - No strict input validation on types

---

## 8. Database Integrity Issues

**Potential Data Issues:**
- Duplicate judul_agenda possible via API
- end_date < start_date possible via direct database access
- Invalid dates if timezone not handled properly
- Empty required fields if validation bypassed

**No Database Constraints:**
- Missing NOT NULL on required fields
- No CHECK constraints for date relationships
- No unique indexes on business keys

---

## Recommendations

### Priority 1 (Critical - Do First)

1. **Add FormRequest Validation for Filament**
   ```php
   // Create AgendaFormRequest with validation rules
   // Create BidangFormRequest with validation rules
   // Use in Filament Pages or Resource
   ```
   - Ensures server-side validation on form submission
   - Consistent with API validation approach

2. **Add Date Relationship Validation**
   - Validate end_date >= start_date
   - Add to KalenderFeedRequest and AgendaFormRequest
   - Add database CHECK constraint

3. **Centralize Bidang Validation**
   - Remove duplicate validation in BidangForm schema
   - Use BidangService rules in Filament schema
   - Or create shared FormRequest

4. **Add Input Sanitization**
   - Extend BaseService::sanitize() for Agenda model
   - Strip HTML from deskripsi and location
   - Normalize whitespace

### Priority 2 (High - Do Second)

5. **Add Date Range Limits**
   - Max 1 year range for calendar API
   - Rate limiting per IP already in place, but add soft limits

6. **Create Validation Service**
   - Consolidate validation rules in single location
   - Share between Filament and API endpoints

7. **Add Missing Validation Rules**
   - `judul_agenda`: Add min:3 (already maxLength:100)
   - Set judul_agenda as unique if applicable
   - Add format validation for location

8. **Create Agenda API Endpoints**
   - POST /api/agenda (create with validation)
   - PUT /api/agenda/{id} (update with validation)
   - DELETE /api/agenda/{id} (delete with authorization)

### Priority 3 (Medium - Do Third)

9. **Add Validation Tests**
   - Unit tests for validation rules
   - Feature tests for API endpoints
   - Test validation bypasses

10. **Database Constraints**
    - Add NOT NULL constraints for required fields
    - Add CHECK constraint for date relationships
    - Add unique indexes on slug fields

11. **Consistent Error Messages**
    - Standardize to either English or Indonesian
    - Include field names in messages
    - Document API error responses

12. **Add Soft Deletes to Agenda**
    - Consistency with Bidang model
    - Better audit trail

### Priority 4 (Nice to Have)

13. **Custom Validation Rules**
    - Create custom rule for date range limits
    - Create rule for special characters in names

14. **API Documentation**
    - Document validation rules for each endpoint
    - Include example error responses

15. **Admin Settings**
    - Allow admin to configure date range limits
    - Allow admin to configure max bidang per agenda

---

## Code Examples for Implementation

### Recommended: Unified Validation Approach

**Option A: Service-Based (Recommended for Filament + API)**

```php
// app/Validations/AgendaValidator.php
class AgendaValidator
{
    public static function rules(?int $id = null): array
    {
        return [
            'judul_agenda' => ['required', 'string', 'min:3', 'max:100', 'unique:agenda,judul_agenda,'.$id],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'location' => ['required', 'string', 'min:3', 'max:100'],
            'start_date' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_date' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:start_date'],
            'is_published' => ['boolean'],
            'bidang_id' => ['array', 'min:1'],
            'bidang_id.*' => ['integer', 'exists:bidang,id'],
        ];
    }
    
    public static function messages(): array
    {
        return [
            'judul_agenda.required' => 'Judul agenda wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            // ... more messages
        ];
    }
    
    public static function sanitize(array $data): array
    {
        return [
            'judul_agenda' => trim($data['judul_agenda'] ?? ''),
            'deskripsi' => strip_tags($data['deskripsi'] ?? ''),
            'location' => strip_tags($data['location'] ?? ''),
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? false),
            'bidang_id' => $data['bidang_id'] ?? [],
        ];
    }
}

// Use in Controller
public function store(Request $request)
{
    $validated = $request->validate(AgendaValidator::rules());
    $sanitized = AgendaValidator::sanitize($validated);
    Agenda::create($sanitized);
}

// Use in Filament
class AgendaForm
{
    public static function getComponents(): array
    {
        $rules = AgendaValidator::rules();
        // Use $rules to configure Filament components
    }
}
```

**Option B: FormRequest (Simpler for API)**

```php
// app/Http/Requests/StoreAgendaRequest.php
class StoreAgendaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'judul_agenda' => ['required', 'string', 'min:3', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'location' => ['required', 'string', 'min:3', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_published' => ['boolean'],
            'bidang_id' => ['array', 'min:1'],
            'bidang_id.*' => ['integer', 'exists:bidang,id'],
        ];
    }
}

// Use in Controller
public function store(StoreAgendaRequest $request)
{
    $validated = $request->validated();
    Agenda::create($validated);
}
```

---

## Summary Table

| Aspect | Current State | Recommended | Effort |
|--------|---------------|-------------|--------|
| FormRequest Usage | Minimal (1 only) | All endpoints | Medium |
| Filament Validation | UI-only | Server-side + UI | Medium |
| Model Validation | None | Service layer | Low |
| Input Sanitization | Bidang only | All models | Low |
| Date Range Validation | Missing | Add | Low |
| API Endpoints | Limited | CRUD operations | High |
| Test Coverage | Unknown | Add tests | High |
| Database Constraints | Minimal | Add constraints | Low |
| **Overall Score** | **4/10** | **8+/10** | **Medium** |

---

## Files Affected by Recommendations

```
app/
├── Http/
│   ├── Requests/
│   │   ├── StoreAgendaRequest.php (NEW)
│   │   ├── UpdateAgendaRequest.php (NEW)
│   │   └── KalenderFeedRequest.php (MODIFY)
│   └── Controllers/
│       └── AgendaController.php (MODIFY)
├── Services/
│   ├── AgendaService.php (NEW)
│   └── BidangService.php (MODIFY)
├── Validations/ (NEW FOLDER)
│   ├── AgendaValidator.php (NEW)
│   └── BidangValidator.php (NEW)
└── Models/
    └── Agenda.php (MODIFY - add sanitization)

database/
├── migrations/
│   └── XXXX_add_constraints.php (NEW)
└── seeders/
    └── ValidationTestSeeder.php (NEW)

tests/
├── Feature/
│   ├── AgendaValidationTest.php (NEW)
│   └── BidangValidationTest.php (NEW)
└── Unit/
    └── ValidatorTest.php (NEW)
```

---

## Conclusion

The AgendaArpus project has a **functional but fragmented validation implementation**. While individual components work, they are not consistently applied across all entry points. The main risk is that Filament form validation can be bypassed completely, and there's no API for creating/updating Agenda records with proper validation.

**Recommended immediate action:** Implement server-side validation using FormRequest classes for all endpoints, particularly for Filament panel submissions.
