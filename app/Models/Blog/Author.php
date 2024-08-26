<?php

namespace App\Models\Blog;

use App\Models\Blog\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Author extends Model
{
    use HasFactory;

    protected $table = 'blog_authors';

    protected $fillable = [
        'name',
        'email',
        'photo',
        'bio',
        'github_handle',
        'twitter_handle'
    ];

    public function posts(): HasMany{
        return $this->hasMany(Post::class, 'blog_author_id');
    }
}
