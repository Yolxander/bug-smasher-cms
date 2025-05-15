<?php

namespace App\Http\Controllers;

use App\Models\DocumentationPage;
use Illuminate\Http\Request;

class DocumentationPageController extends Controller
{
    public function preview(DocumentationPage $documentationPage)
    {
        return view('documentation.preview', compact('documentationPage'));
    }
}
