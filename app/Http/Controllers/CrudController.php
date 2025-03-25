<?php
namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // <-- Import Hash
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class CrudController extends Controller
{
    public function MassInsert(Request $request)
    {
        $TableName     = $request->TableName;
        $tableColumns  = Schema::getColumnListing($TableName);
        $data          = $request->except(['_token', 'id', 'TableName', 'PostRoute']);
        $rules         = [];
        $uploadedFiles = [];

        foreach ($tableColumns as $column) {
            if ($request->hasFile($column)) {
                $rules[$column] = 'file|mimes:pdf,doc,docx,jpg,jpeg,png,webp|max:80000';
            } else {
                $rules[$column] = 'nullable';
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        foreach ($data as $key => $value) {
            if ($request->hasFile($key)) {
                $uploadedFiles[$key] = $this->moveUploadedFile($request->file($key));
            }
        }

        try {
            $insertData = array_merge($data, $uploadedFiles);

            // Hash any "password"/"Password" fields
            foreach ($insertData as $key => $value) {
                if (strtolower($key) === 'password' && ! empty($value)) {
                    $insertData[$key] = Hash::make($value);
                }
            }

            DB::table($TableName)->insert($insertData);

            return redirect()->back()->with('status', 'The action executed successfully');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to insert data. ' . $e->getMessage());
        }
    }

    private function moveUploadedFile($file)
    {
        if (! $file) {
            return null;
        }

        $destinationPath = public_path('assets/docs');
        $fileName        = time() . '_' . $file->getClientOriginalName();
        $file->move($destinationPath, $fileName);

        return 'assets/docs/' . $fileName;
    }

    private function removeNullValues($array)
    {
        return array_filter($array, function ($value) {
            return ! is_null($value);
        });
    }

    public function MassUpdate(Request $request)
    {
        $TableName     = $request->TableName;
        $tableColumns  = Schema::getColumnListing($TableName);
        $data          = $request->except(['_token', 'id', 'TableName', 'PostRoute', '_method']);
        $rules         = [];
        $uploadedFiles = [];

        foreach ($tableColumns as $column) {
            if ($request->hasFile($column)) {
                $rules[$column] = 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:80000';
            } else {
                $rules[$column] = 'nullable';
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        foreach ($data as $key => $value) {
            if ($request->hasFile($key)) {
                $uploadedFiles[$key] = $this->moveUploadedFile($request->file($key));
            }
        }

        try {
            $updateData = array_merge($data, $uploadedFiles);

            // Hash any "password"/"Password" fields
            foreach ($updateData as $key => $value) {
                if (strtolower($key) === 'password' && ! empty($value)) {
                    $updateData[$key] = Hash::make($value);
                }
            }

            DB::table($TableName)->where('id', $request->id)->update($this->removeNullValues($updateData));

            return redirect()->back()->with('status', 'The action executed successfully');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to update data. ' . $e->getMessage());
        }
    }

    public function MassDelete(Request $request)
    {
        $TableName = $request->TableName;
        $id        = $request->id;

        try {
            if ($TableName == "ebs_structures") {
                $u = DB::table($TableName)->where('id', $id)->first();
                DB::table('users')->where('UserID', $u->UserID)->delete();
            }

            DB::table($TableName)->where('id', $id)->delete();

            return redirect()->back()->with('status', 'Record deleted successfully');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to delete the record. ' . $e->getMessage());
        }
    }

    public function getTableColumns(Request $request)
    {
        try {
            $tableName = $request->input('TableName');

            if (! Schema::hasTable($tableName)) {
                return redirect()->back()->with('error', 'Table does not exist.');
            }

            $columns = DB::select("DESCRIBE $tableName");

            return redirect()->back()->with('columns', $columns);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to retrieve table columns. ' . $e->getMessage());
        }
    }
}