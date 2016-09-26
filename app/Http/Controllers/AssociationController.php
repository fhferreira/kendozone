<?php

namespace App\Http\Controllers;

use App\Association;
use App\Federation;
use App\Http\Requests\AssociationRequest;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use URL;

class AssociationController extends Controller
{
    // Only Super Admin and Association President can manage Associations

    /**
     * Display a listing of the resource.
     *
     * @return Association|View
     */
    public function index()
    {
        if (Request::ajax()) {
            return Association::fillSelect();
        } else {
            $associations = Association::with('president', 'federation.country')
                ->forUser(Auth::user())
                ->where('id', '>', 1)
                ->get();
            $currentModelName = trans_choice('core.association', 1);
            return view('associations.index', compact('associations', 'currentModelName'));
        }
    }


    /**
     * Show the form for creating a new resource.
     * @return View
     * @throws AuthorizationException
     */
    public function create()
    {
        $association = new Association;
        $federation = new Collection;

        if (Auth::user()->cannot('create', new Association)) {
            throw new AuthorizationException();
        }

//        dd(Auth::user()->role);
        $users = User::forUser(Auth::user())->pluck('name', 'id');
        $federations = Federation::forUser(Auth::user())->pluck('name', 'id');
        $submitButton = trans('core.addModel', ['currentModelName' => trans_choice('core.association', 1)]);
        return view('associations.form', compact('association', 'users', 'federation', 'federations', 'submitButton')); //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AssociationRequest $request
     * @return Response
     * @throws AuthorizationException
     */
    public function store(AssociationRequest $request)
    {
        // Assoc Policy
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isFederationPresident()) {
            throw new AuthorizationException();
        }

        try {
            $association = Association::create($request->all());
            $msg = trans('msg.association_create_successful', ['name' => $association->name]);
            flash()->success($msg);
            return redirect(route("associations.index"));

        } catch (QueryException $e) {

            $msg = trans('msg.association_president_already_exists');
            flash()->error($msg);
            return redirect()->back();

        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return View
     */
    public function show($id)
    {

        $association = Association::findOrFail($id);
        return view('associations.show', compact('association'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return View
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $currentModelName = trans_choice('core.association', 1);
        $association = Association::findOrFail($id);
        $federation = $association->federation;

        if (Auth::user()->cannot('edit', $association)) {
            throw new AuthorizationException();
        }

        $users = User::forUser(Auth::user())->pluck('name', 'id');
        $federations = Federation::forUser(Auth::user())->pluck('name', 'id');

        return view('associations.form', compact('currentModelName', 'association', 'users', 'federations', 'federation'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AssociationRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function update(AssociationRequest $request, $id)
    {
        $association = Association::findOrFail($id);

        if (Auth::user()->cannot('update', $association)) {
            throw new AuthorizationException();
        }
        try {
            $association->update($request->except(['federation_id']));
            $msg = trans('msg.association_edit_successful', ['name' => $association->name]);
            flash()->success($msg);
            return redirect(route('associations.index'));

        } catch (QueryException $e) {

            $msg = trans('msg.association_president_already_exists');
            flash()->error($msg);
            return redirect()->back();

        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $associationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($associationId)
    {
        $association = Association::find($associationId);
//        dd($association);
        if ($association->delete()) {
            return Response::json(['msg' => trans('msg.association_delete_successful', ['name' => $association->name]), 'status' => 'success']);
        } else {
            return Response::json(['msg' => trans('msg.association_delete_error', ['name' => $association->name]), 'status' => 'error']);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)

    {
        $association = Association::withTrashed()->find($id);
        if ($association->restore()) {
            return Response::json(['msg' => trans('msg.association_restored_successful', ['name' => $association->name]), 'status' => 'success']);
        } else {
            return Response::json(['msg' => trans('msg.association_restored_error', ['name' => $association->name]), 'status' => 'error']);
        }
    }


    public function changePresident()
    {
        // Open Transaction
        // Get the current president
        // Set the new president
        // Save
        // Close transaction
    }

    public static function myAssociations()
    {
        return Association::fillSelect();
    }



}
