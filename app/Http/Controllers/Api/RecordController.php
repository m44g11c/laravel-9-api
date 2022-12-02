<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\storeRecordRequest;
use App\Http\Resources\RecordResource;
use App\Models\Category;
use App\Models\Record;
use App\Models\User;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    const PAGINATION = 10;

    public  function __construct()
    {
        $this->authorizeResource(Record::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->role == 'manager') {
            $managerEmployees = User::where('manager_id', $request->user()->id)->pluck('id')->toArray();
            $records = Record::select(['id', 'name', 'image', 'category_id', 'user_id'])->whereIn('user_id', $managerEmployees);
        } else {
            $employee = $request->user();
            $records = $employee->records();
        }

        return RecordResource::collection($records->paginate(self::PAGINATION));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecordRequest $request)
    {
        $image_path = $request->file('image')->store('image', 'public');
        $employee = $request->user();
        $category = Category::where('id', $request->category)->firstOrFail();

        $record = new Record([
            'name' => $request->name,
            'image' => $image_path,
        ]);

        $employee->records()->save($record);
        $category->records()->save($record);

        return new RecordResource($record);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Record  $record
     * @return \Illuminate\Http\Response
     */
    public function update(StoreRecordRequest $request, Record $record)
    {
        $record->update($request->all());

        return new RecordResource($record);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Record  $record
     * @return \Illuminate\Http\Response
     */
    public function destroy(Record $record)
    {
        $record->delete();

        return response(null, 204);
    }
}
