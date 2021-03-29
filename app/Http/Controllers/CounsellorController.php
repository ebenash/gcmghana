<?php

namespace App\Http\Controllers;
use DB;
use SimpleXLSX;
use App\Models\Counsellor;
use Illuminate\Http\Request;

class CounsellorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $data['counsellors'] = Counsellor::all();
        return view('counsellors',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'counsellor_name'=>'required',
            'phone'=>'required',
        ]);
        // dd($request->all());

        $counsellor = new Counsellor;
        
        $counsellor->name = $request->input('counsellor_name');
        $counsellor->phone = $this->formatphonenumber($request->input('phone'));
        $counsellor->email = $request->input('email');

        $counsellor->save();

        return back()->with('success','Counsellor Successfully was Created!');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls|max:25000'
        ]);
        $path = $request->file('import_file')->getRealPath();
        // dd($path);
        if ( $xlsx = SimpleXLSX::parse($path) ) {
            $counsellors =$xlsx->rows();

            if($request->input('headers')){
                $removed = array_shift($counsellors);
            }else{
                $i=1;
                $removed = array();
                foreach($counsellors[0] as $col){
                    array_push($removed,'Column '.$i);
                    $i++;
                }
            }

            $unique = array_unique($counsellors, SORT_REGULAR);
            $duplicates = count($counsellors) - count($unique);
            $counsellors = $unique;

            $success = 0;
            $fail = 0;
            $failed = array();
            $successful = array();
            try{
                DB::beginTransaction();
                // dd($counsellors);
                foreach($counsellors as $new){
                        $counsellor = array();
                        $counsellor['name'] = $new[0];
                        $counsellor['phone'] = $this->formatphonenumber($new[1]);
                        $counsellor['email'] = $new[2];
                        array_push($successful,$counsellor);
                }
                
                // dd($successful);
                foreach(array_chunk($successful,1000) as $chunk){
                    $result = DB::table('counsellors')->insert($chunk);
                }

                $data = [
                    'success'=> $success,
                    'fail'=> $fail,
                    'headers' => $removed,
                    'import_errors' => $failed
                ];

                DB::commit();
                return back()->with('success','Counsellors Imported Successfully!');
            }catch(\Exception $e){       
                DB::rollback();
                return back()->with('error','Counsellor Import Failed.');
            }    
        } else {
            return back()->with('error', SimpleXLSX::parseError());
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Counsellor  $counsellor
     * @return \Illuminate\Http\Response
     */
    public function edit(Counsellor $counsellor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Counsellor  $counsellor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Counsellor $counsellor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Counsellor  $counsellor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Counsellor $counsellor)
    {
        //
    }
}
