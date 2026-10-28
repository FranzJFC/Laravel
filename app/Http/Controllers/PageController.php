<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function about()
    {
        $data = [
            'course' => 'Web System Technologies',
            'module' => 'Laravel Foundations',
        ];

        return view('about', $data);
    }

    public function task(int $id)
    {
        return view('task', [
            'title' => $id === 15 ? 'Buy groceries' : "Task #{$id}",
            'id' => $id,
        ]);
    }
}
