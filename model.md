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
