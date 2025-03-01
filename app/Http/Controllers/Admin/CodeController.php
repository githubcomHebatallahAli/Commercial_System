<?php

namespace App\Http\Controllers\Admin;

use App\Models\Code;
use Illuminate\Http\Request;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CodeRequest;
use App\Http\Resources\Admin\CodeResource;

class CodeController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
        $this->authorize('manage_users');

        $Codes = Code::get();
        return response()->json([
            'data' => CodeResource::collection($Codes),
            'message' => "Show All Codes Successfully."
        ]);
    }


    public function create(CodeRequest $request)
    {
        $this->authorize('manage_users');

           $Code =Code::create ([
                "code" => $request-> code,
                "discount" => $request-> discount,
                "status" => 'notActive',
                "type" => $request->type
            ]);
           $Code->save();
           return response()->json([
            'data' =>new CodeResource($Code),
            'message' => "Code Created Successfully."
        ]);
        }


    public function edit(string $id)
    {
        $this->authorize('manage_users');
        $Code = Code::find($id);

        if (!$Code) {
            return response()->json([
                'message' => "Code not found."
            ], 404);
        }

        return response()->json([
            'data' =>new CodeResource($Code),
            'message' => "Edit Code By ID Successfully."
        ]);
    }



    public function update(CodeRequest $request, string $id)
    {
        $this->authorize('manage_users');
       $Code =Code::findOrFail($id);

       if (!$Code) {
        return response()->json([
            'message' => "Code not found."
        ], 404);
    }
       $Code->update([
        "code" => $request-> code,
        "discount" => $request-> discount,
        "status" =>  $request-> status,
        "type" =>  $request-> type
        ]);

       $Code->save();
       return response()->json([
        'data' =>new CodeResource($Code),
        'message' => " Update Code By Id Successfully."
    ]);

  }

  public function destroy(string $id)
  {
      return $this->destroyModel(Code::class, CodeResource::class, $id);
  }

  public function showDeleted()
  {
    $this->authorize('manage_users');
$Codes=Code::onlyTrashed()->get();
return response()->json([
    'data' =>CodeResource::collection($Codes),
    'message' => "Show Deleted Codes Successfully."
]);

}

public function restore(string $id)
{
   $this->authorize('manage_users');
$Code = Code::withTrashed()->where('id', $id)->first();
if (!$Code) {
    return response()->json([
        'message' => "Code not found."
    ], 404);
}
$Code->restore();
return response()->json([
    'data' =>new CodeResource($Code),
    'message' => "Restore Code By Id Successfully."
]);
}

  public function forceDelete(string $id)
  {
      return $this->forceDeleteModel(Code::class, $id);
  }

  public function active(string $id)
  {
      $this->authorize('manage_users');
      $Code =Code::findOrFail($id);

      if (!$Code) {
       return response()->json([
           'message' => "Code not found."
       ], 404);
   }

      $Code->update(['status' => 'active']);

      return response()->json([
          'data' => new CodeResource($Code),
          'message' => 'Code has been active.'
      ]);
  }

  public function notActive(string $id)
  {
      $this->authorize('manage_users');
      $Code =Code::findOrFail($id);

      if (!$Code) {
       return response()->json([
           'message' => "Code not found."
       ], 404);
   }

      $Code->update(['status' => 'notActive']);

      return response()->json([
          'data' => new CodeResource($Code),
          'message' => 'Code has been notActive.'
      ]);
  }

  public function pounds(string $id)
  {
      $this->authorize('manage_users');
      $Code =Code::findOrFail($id);

      if (!$Code) {
       return response()->json([
           'message' => "Code not found."
       ], 404);
   }

      $Code->update(['type' => 'pounds']);

      return response()->json([
          'data' => new CodeResource($Code),
          'message' => 'Code has been Discount in pounds.'
      ]);
  }

  public function percentage(string $id)
  {
      $this->authorize('manage_users');
      $Code =Code::findOrFail($id);

      if (!$Code) {
       return response()->json([
           'message' => "Code not found."
       ], 404);
   }

      $Code->update(['type' => 'percentage']);

      return response()->json([
          'data' => new CodeResource($Code),
          'message' => 'Code has been Percentage discount.'
      ]);
  }

}
