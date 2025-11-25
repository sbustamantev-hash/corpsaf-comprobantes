<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rule;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'dni',
        'telefono',
        'password',
        'role',
        'area_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Reglas de validación para el modelo
     * 
     * @return array
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'dni' => ['required', 'string', 'max:20', 'unique:users,dni'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(RoleEnum::all())],
            'area_id' => ['nullable', 'exists:areas,id'],
        ];
    }

    /**
     * Relación con área
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Relación con comprobantes
     */
    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class);
    }

    /**
     * Verificar si es super administrador
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === RoleEnum::ADMIN;
    }

    /**
     * Verificar si es administrador de área
     * 
     * @return bool
     */
    public function isAreaAdmin(): bool
    {
        return $this->role === RoleEnum::AREA_ADMIN;
    }

    /**
     * Verificar si es operador/trabajador
     * 
     * @return bool
     */
    public function isOperador(): bool
    {
        return RoleEnum::isOperador($this->role);
    }

    /**
     * Verificar si puede gestionar áreas (solo super admin)
     * 
     * @return bool
     */
    public function canManageAreas(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verificar si puede gestionar usuarios (solo super admin)
     * 
     * @return bool
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verificar si puede ver comprobantes de un área específica
     * 
     * @param int|null $areaId
     * @return bool
     */
    public function canViewAreaComprobantes(?int $areaId): bool
    {
        // Super admin puede ver todo
        if ($this->isAdmin()) {
            return true;
        }

        // Area admin solo puede ver su área
        if ($this->isAreaAdmin()) {
            return $this->area_id === $areaId;
        }

        // Operador solo puede ver los suyos (se verifica por user_id)
        return false;
    }

    /**
     * Obtener la etiqueta legible del rol
     * 
     * @return string
     */
    public function getRoleLabelAttribute(): string
    {
        return RoleEnum::label($this->role);
    }
}
