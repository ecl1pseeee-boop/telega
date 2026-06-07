<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $type
 * @property string|null $title
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \App\Models\User $owner
 * @method static \Database\Factories\ChatFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Chat extends Model
{
    /** @use HasFactory<\Database\Factories\ChatFactory> */
    use HasFactory;

    public const TYPES = [
        'direct',
        'group',
    ];

    protected $table = 'chats';
    protected $fillable = [
        'type',
        'title',
        'created_by'
    ];

    /*
     * Создатель чата
     */
    public function owner(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
     * Все участники чата
     */
    public function members(): BelongsToMany {
        return $this->belongsToMany(User::class, 'chat_members', 'chat_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /*
     * Все сообщения в чате
     */
    public function messages(): HasMany {
        return $this->hasMany(Message::class, 'chat_id');
    }

    /*
     * Является ли участником чата?
     */
    public function isUserMember(User $user): bool {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /*
     * Является ли владельцем чата?
     */
    public function isUserOwner(User $user): bool {
        return $this->members()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    /*
     * Добавить пользователя в чат
     */
    public function addUserToChat(User $user): void
    {
        $this->members()->attach($user, ['role' => 'member']);
    }

    /*
     * Удалить пользователя из чата
     */
    public function removeUserFromChat(User $user): void
    {
        $this->members()->detach($user);
    }
}
