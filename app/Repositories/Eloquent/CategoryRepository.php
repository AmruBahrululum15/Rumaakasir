<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Str;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAll($search = null)
    {
        return Category::when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);
    }

    public function findById($id)
    {
        return Category::findOrFail($id);
    }

    public function create(array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        return Category::create($data);
    }

    public function update($id, array $data)
    {
        $category = $this->findById($id);

        $data['slug'] = Str::slug($data['name']);

        $category->update($data);

        return $category;
    }

    public function delete($id)
    {
        return $this->findById($id)->delete();
    }
}