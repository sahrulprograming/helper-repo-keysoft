# Model Concept

Semua model di library ini mengikuti pola seperti `MsCountry`.
Tujuannya agar koneksi tenant, audit trail, dan konvensi schema konsisten di semua service.

## Requirements

Setiap model wajib punya:

- `extends BaseModelTenant`
- `use HasFactory, AuditedBy;`
- `protected $connection = 'tenant';`
- `protected $table = '...';` sesuai nama tabel
- `protected $primaryKey = 'id';`
- `public $incrementing = true;`
- `protected $keyType = 'integer';` (atau `int` bila memang begitu)
- `protected $guarded = ['created_at', 'updated_at'];`

## Example (MsCountry Style)

```php
<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsCountry extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_country';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];
}
```

## Namespace Rules

- Namespace harus mengikuti struktur folder. Contoh: `src/Models/Master/Accounting/MsCOA.php` -> `namespace Keysoft\HelperLibrary\Models\Tenant\Master\Accounting;`
- Jangan gunakan `App\Models` di package ini.

## Accounting Module Mapping

Relasi model accounting harus mengikuti foreign key yang ada di migration:

- `MsCOA` (`ms_coa`)
  - `category_id` -> `belongsTo(MsCategoryCOA::class, 'category_id')`
  - `account_type_id` -> `belongsTo(MsAccountType::class, 'account_type_id')`
  - `currency_id` -> `belongsTo(MsCurrency::class, 'currency_id')`
  - `parent_id` -> `belongsTo(MsCOA::class, 'parent_id')`
  - `bank_id` -> `belongsTo(MsBank::class, 'bank_id')`
- `MsCategoryCOA` (`ms_category_coa`)
  - inverse: `hasMany(MsCOA::class, 'category_id')`
- `MsAccountType` (`ms_account_type`)
  - inverse: `hasMany(MsCOA::class, 'account_type_id')`
- `MsAccountMappingType` (`ms_account_mapping_type`)
  - `coa_id` -> `belongsTo(MsCOA::class, 'coa_id')`
- `MsAccountMapping` (`ms_account_mapping`)
  - `coa_id` -> `belongsTo(MsCOA::class, 'coa_id')`
- `MsAccountMappingInventory` (`ms_account_mapping_inventory`)
  - `coa_id` -> `belongsTo(MsCOA::class, 'coa_id')`
- `MsForex` (`trans_rate_policy`)
  - `currency_id` -> `belongsTo(MsCurrency::class, 'currency_id')`
