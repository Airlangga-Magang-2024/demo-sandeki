<?php

namespace App\Models\Blog;

use App\Models\Comment;
use Spatie\Tags\HasTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;
    use HasTags;

    protected $table = 'blog_posts';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'blog_author_id',
        'published_at',
        'image'
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    public function author(): BelongsTo{
        return $this->belongsTo(Author::class, 'blog_author_id');
    }
    
    public function category(): BelongsTo{
        return $this->belongsTo(Category::class, 'blog_category_id');
    }

    public function comments(): MorphMany{
        return $this->morphMany(Comment::class, 'commentable');
    }
}
