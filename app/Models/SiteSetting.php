<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Informasi website yang dapat diubah admin (tabel site_settings).
 *
 * Tabel ini hanya memiliki updated_at, sehingga CREATED_AT dinonaktifkan.
 * Daftar kunci yang sah didefinisikan di config/jnet.php.
 */
class SiteSetting extends Model
{
    const CREATED_AT = null;

    protected $table = 'site_settings';

    protected $primaryKey = 'id_setting';

    protected $fillable = [
        'nama_setting',
        'nilai_setting',
    ];

    /**
     * Admin yang terakhir memperbarui informasi (relasi "memperbarui").
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}
