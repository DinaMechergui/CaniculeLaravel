<?php

namespace App\BackOffice\Controllers;

use App\BackOffice\Requests\StoreCategorieConseilRequest;
use App\BackOffice\Requests\UpdateCategorieConseilRequest;
use App\Http\Controllers\Controller;
use App\Models\CategorieConseil;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategorieConseilController extends Controller
{
    // Liste paginée, avec le nombre de conseils par catégorie.
    public function index(): View
    {
        $categories = CategorieConseil::withCount('conseils')->latest()->paginate(10);

        return view('back.categories-conseil.index', compact('categories'));
    }

    public function create(): View
    {
        return view('back.categories-conseil.create', [
            'categorie' => new CategorieConseil(),
        ]);
    }

    public function store(StoreCategorieConseilRequest $request): RedirectResponse
    {
        CategorieConseil::create($request->validated());

        return redirect()->route('admin.categories-conseil.index')
            ->with('success', 'La catégorie a été créée avec succès.');
    }

    // Détail : la catégorie et ses conseils.
    public function show(CategorieConseil $categorieConseil): View
    {
        return view('back.categories-conseil.show', [
            'categorie' => $categorieConseil,
            'conseils' => $categorieConseil->conseils()->latest()->get(),
        ]);
    }

    public function edit(CategorieConseil $categorieConseil): View
    {
        return view('back.categories-conseil.edit', [
            'categorie' => $categorieConseil,
        ]);
    }

    public function update(UpdateCategorieConseilRequest $request, CategorieConseil $categorieConseil): RedirectResponse
    {
        $categorieConseil->update($request->validated());

        return redirect()->route('admin.categories-conseil.index')
            ->with('success', 'La catégorie a été modifiée avec succès.');
    }

    // Suppression refusée si la catégorie a encore des conseils.
    public function destroy(CategorieConseil $categorieConseil): RedirectResponse
    {
        if ($categorieConseil->conseils()->exists()) {
            return redirect()->route('admin.categories-conseil.index')
                ->with('error', 'Impossible de supprimer cette catégorie : des conseils y sont encore rattachés.');
        }

        $categorieConseil->delete();

        return redirect()->route('admin.categories-conseil.index')
            ->with('success', 'La catégorie a été supprimée avec succès.');
    }
}
