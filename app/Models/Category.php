<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * CATEGORY MODEL - Model untuk kategori tickets
 * 
 * PENJELASAN KE DOSEN:
 * Category digunakan untuk mengklasifikasikan jenis masalah yang dilaporkan customer.
 * Contoh kategori: Perbaikan Mesin, Quality Control, Safety Issue, dll.
 * 
 * FIELD:
 * - name: Nama kategori (contoh: "Perbaikan Mesin")
 * - slug: URL-friendly version dari name (auto-generated)
 * - description: Penjelasan detail kategori
 * 
 * AUTO-SLUG:
 * Slug otomatis dibuat dari name saat data disimpan
 * Contoh: "Perbaikan Mesin" -> "perbaikan-mesin"
 * Berguna untuk SEO dan URL yang clean
 */
class Category extends Model
{
    use HasFactory;

    /**
     * Field yang boleh diisi secara mass-assignment
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Boot method - dipanggil saat model di-inisialisasi
     * Digunakan untuk auto-generate slug dari name
     */
    public static function booted()
    {
        // Event listener: setiap kali Category mau disave (create/update)
        static::saving(function ($model) {
            // Jika slug kosong tapi name ada, generate slug otomatis
            if (empty($model->slug) && !empty($model->name)) {
                $model->slug = \Illuminate\Support\Str::slug($model->name);
            }
        });
    }
}
