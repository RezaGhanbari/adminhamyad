<?php

namespace TCG\Voyager\Http\Controllers;

use App\Course;
use App\Pack;
use App\Provider;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;
use TCG\Voyager\Models\DataType;

class VoyagerBreadController extends Controller
{
    //***************************************
    //               ____
    //              |  _ \
    //              | |_) |
    //              |  _ <
    //              | |_) |
    //              |____/
    //
    //      Browse our Data Type (B)READ
    //
    //****************************************

    public function index(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $request->segment(2);

        // GET THE DataType based on the slug
        $dataType = DataType::where('slug', '=', $slug)->first();

        // Next Get the actual content from the MODEL that corresponds to the slug DataType
        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? call_user_func([$dataType->model_name, 'all'])
            : DB::table($dataType->name)->get(); // If Model doest exist, get data from table name

        $view = 'voyager::bread.browse';

        if (view()->exists("admin.$slug.browse")) {
            $view = "admin.$slug.browse";
        } elseif (view()->exists("voyager::$slug.browse")) {
            $view = "voyager::$slug.browse";
        }

        return view($view, compact('dataType', 'dataTypeContent'));
    }

    //***************************************
    //                _____
    //               |  __ \
    //               | |__) |
    //               |  _  /
    //               | | \ \
    //               |_|  \_\
    //
    //  Read an item of our Data Type B(R)EAD
    //
    //****************************************

    public function show(Request $request, $id)
    {
        $slug = $request->segment(2);
        $dataType = DataType::where('slug', '=', $slug)->first();

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? call_user_func([$dataType->model_name, 'find'], $id)
            : DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

        return view('voyager::bread.read', compact('dataType', 'dataTypeContent'));
    }

    //***************************************
    //                ______
    //               |  ____|
    //               | |__
    //               |  __|
    //               | |____
    //               |______|
    //
    //  Edit an item of our Data Type BR(E)AD
    //
    //****************************************

    public function edit(Request $request, $id)
    {
        $slug = $request->segment(2);
        $dataType = DataType::where('slug', '=', $slug)->first();
        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? call_user_func([$dataType->model_name, 'find'], $id)
            : DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

        $view = 'voyager::bread.edit-add';

        if (view()->exists("admin.$slug.edit-add")) {
            $view = "admin.$slug.edit-add";
        } elseif (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }
        if($slug=='courses')
            $view="admin.$slug.edit-add";

        return view($view, compact('dataType', 'dataTypeContent'));
    }

    // POST BR(E)AD
    public function update(Request $request, $id)
    {
        $slug = $request->segment(2);
        $dataType = DataType::where('slug', '=', $slug)->first();
        $data = call_user_func([$dataType->model_name, 'find'], $id);
        $this->insertUpdateData($request, $slug, $dataType->editRows, $data);
        if($slug=='courses' && isset($request['tags'])){
            $tags=$request['tags'];
            $data->tags()->detach();
            if(! empty($tags)){
                $tagss=explode(',',$tags);
                foreach ($tagss as $tag){
                    $tag=Tag::where('tag_name',$tag)->first();
                    try{
                        $data->tags()->attach($tag->id);
                    }
                    catch ( \Illuminate\Database\QueryException $e){
                        return view('errors.500');
                    }
                }
            }
        }
        if($slug=='courses'){
            $provider=$request['provider_id'];
            $data->provider()->detach();
            try{
                $data->provider()->attach($provider);
            }
            catch ( \Illuminate\Database\QueryException $e){
                return view('errors.500');
            }
        }
        return redirect()
            ->route("{$dataType->slug}.index")
            ->with([
                'message'    => "Successfully Updated {$dataType->display_name_singular}",
                'alert-type' => 'success',
            ]);
    }

    //***************************************
    //
    //                   /\
    //                  /  \
    //                 / /\ \
    //                / ____ \
    //               /_/    \_\
    //
    //
    // Add a new item of our Data Type BRE(A)D
    //
    //****************************************

    public function create(Request $request)
    {
        $slug = $request->segment(2);
        $dataType = DataType::where('slug', '=', $slug)->first();

        $view = 'voyager::bread.edit-add';

        if (view()->exists("admin.$slug.edit-add")) {
            $view = "admin.$slug.edit-add";
        } elseif (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }

        return view($view, compact('dataType'));
    }

    // POST BRE(A)D
    public function store(Request $request)
    {
        $slug = $request->segment(2);
        $dataType = DataType::where('slug', '=', $slug)->first();

        if (function_exists('voyager_add_post')) {
            $url = $request->url();
            voyager_add_post($request);
        }

        $data = new $dataType->model_name();
        $this->insertUpdateData($request, $slug, $dataType->addRows, $data);
        if($slug=='courses' && isset($request['tags'])){
            $tags=$request['tags'];
           if(! empty($tags)){
               $tagss=explode(',',$tags);
               foreach ($tagss as $tag){
                   $tag=Tag::where('tag_name',$tag)->first();
                   try{
                       $data->tags()->attach($tag->id);
                   }
                   catch ( \Illuminate\Database\QueryException $e){
                       return view('errors.500');
                   }
               }
           }
        }

        if($slug=='courses'){
            $provider=$request['provider_id'];
            $data->provider()->detach();
            try{
                $data->provider()->attach($provider);
            }
            catch ( \Illuminate\Database\QueryException $e){
                return view('errors.500');
            }
        }

        return redirect()
            ->route("{$dataType->slug}.index")
            ->with([
                'message'    => "Successfully Added New {$dataType->display_name_singular}",
                'alert-type' => 'success',
            ]);
    }

    //***************************************
    //                _____
    //               |  __ \
    //               | |  | |
    //               | |  | |
    //               | |__| |
    //               |_____/
    //
    //         Delete an item BREA(D)
    //
    //****************************************

    public function destroy(Request $request, $id)
    {
        $slug = $request->segment(2);
        $dataType = DataType::where('slug', '=', $slug)->first();

        $data = call_user_func([$dataType->model_name, 'find'], $id);

        foreach ($dataType->deleteRows as $row) {
            if ($row->type == 'image') {
                $this->deleteFileIfExists('/uploads/'.$data->{$row->field});

                $options = json_decode($row->details);

                if (isset($options->thumbnails)) {
                    foreach ($options->thumbnails as $thumbnail) {
                        $ext = explode('.', $data->{$row->field});
                        $extension = '.'.$ext[count($ext) - 1];

                        $path = str_replace($extension, '', $data->{$row->field});

                        $thumb_name = $thumbnail->name;

                        $this->deleteFileIfExists('/uploads/'.$path.'-'.$thumb_name.$extension);
                    }
                }
            }
        }

        $data = $data->destroy($id)
            ? [
                'message'    => "Successfully Deleted {$dataType->display_name_singular}",
                'alert-type' => 'success',
            ]
            : [
                'message'    => "Sorry it appears there was a problem deleting this {$dataType->display_name_singular}",
                'alert-type' => 'error',
            ];

        return redirect()->route("{$dataType->slug}.index")->with($data);
    }

    public function insertUpdateData($request, $slug, $rows, $data)
    {
        $rules = [];

        foreach ($rows as $row) {
            $options = json_decode($row->details);
            if (isset($options->rule)) {
                $rules[$row->field] = $options->rule;
            }

            $content = $this->getContentBasedOnType($request, $slug, $row);
            if ($content === null) {
                if (isset($data->{$row->field})) {
                    $content = $data->{$row->field};
                }
                if ($row->field == 'password') {
                    $content = $data->{$row->field};
                }
            }

            $data->{$row->field} = $content;
        }

        $this->validate($request, $rules);

        $data->save();
    }

    public function getContentBasedOnType(Request $request, $slug, $row)
    {
        $content = null;
        switch ($row->type) {
            /********** PASSWORD TYPE **********/
            case 'password':
                $pass_field = $request->input($row->field);

                if (isset($pass_field) && !empty($pass_field)) {
                    return bcrypt($request->input($row->field));
                }
                break;

            /********** CHECKBOX TYPE **********/
            case 'checkbox':
                $checkBoxRow = $request->input($row->field);

                if (isset($checkBoxRow)) {
                    return 1;
                }

                $content = 0;
                break;

            /********** FILE TYPE **********/
            case 'file':
                $file = $request->file($row->field);
                $filename = Str::random(20);
                $path = $slug.'/'.date('F').date('Y').'/';

                $fullPath = $path.$filename.'.'.$file->getClientOriginalExtension();

                Storage::put(config('voyager.storage.subfolder').$fullPath, (string) $file, 'public');

                return $fullPath;
                // no break

            /********** IMAGE TYPE **********/
//            case 'image':
//                if ($request->hasFile($row->field)) {
//                    $storage_disk = 'local';
//                    $file = $request->file($row->field);
//                    $filename = Str::random(20);
//
//                    $path = $slug.'/'.date('F').date('Y').'/';
//                    $fullPath = $path.$filename.'.'.$file->getClientOriginalExtension();
//
//                    $options = json_decode($row->details);
//
//                    if (isset($options->resize) && isset($options->resize->width) && isset($options->resize->height)) {
//                        $resize_width = $options->resize->width;
//                        $resize_height = $options->resize->height;
//                    } else {
//                        $resize_width = 1800;
//                        $resize_height = null;
//                    }
//
//                    $image = Image::make($file)
//                        ->resize($resize_width, $resize_height, function (Constraint $constraint) {
//                            $constraint->aspectRatio();
//                            $constraint->upsize();
//                        })
//                        ->encode($file->getClientOriginalExtension(), 75);
//
//                    Storage::put(config('voyager.storage.subfolder').$fullPath, (string) $image, 'public');
//
//                    if (isset($options->thumbnails)) {
//                        foreach ($options->thumbnails as $thumbnails) {
//                            if (isset($thumbnails->name) && isset($thumbnails->scale)) {
//                                $scale = intval($thumbnails->scale) / 100;
//                                $thumb_resize_width = $resize_width;
//                                $thumb_resize_height = $resize_height;
//                                if ($thumb_resize_width != 'null') {
//                                    $thumb_resize_width = $thumb_resize_width * $scale;
//                                }
//                                if ($thumb_resize_height != 'null') {
//                                    $thumb_resize_height = $thumb_resize_height * $scale;
//                                }
//                                $image = Image::make($file)
//                                    ->resize($thumb_resize_width, $thumb_resize_height, function (Constraint $constraint) {
//                                        $constraint->aspectRatio();
//                                        $constraint->upsize();
//                                    })
//                                    ->encode($file->getClientOriginalExtension(), 75);
//                            } elseif (isset($options->thumbnails) && isset($thumbnails->crop->width) && isset($thumbnails->crop->height)) {
//                                $crop_width = $thumbnails->crop->width;
//                                $crop_height = $thumbnails->crop->height;
//                                $image = Image::make($file)
//                                    ->fit($crop_width, $crop_height)
//                                    ->encode($file->getClientOriginalExtension(), 75);
//                            }
//
//                            Storage::put(config('voyager.storage.subfolder').$path.$filename.'-'.$thumbnails->name.'.'.$file->getClientOriginalExtension(),
//                                (string) $image, 'public');
//                        }
//                    }
//
//                    return $fullPath;
//                }
//                break;

            /********** ALL OTHER TEXT TYPE **********/
            default:
                return $request->input($row->field);
                // no break
        }

        return $content;
    }

    public function generate_views(Request $request)
    {
        //$dataType = DataType::where('slug', '=', $slug)->first();
    }

    private function deleteFileIfExists($path)
    {
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    public function showall($id)
    {
        $provider = Provider::findorfail($id);

        $courses=$provider->courses;
        return view('showallcourses')->with(['courses'=>$courses,'provider'=>$provider]);
    }

    public function participation($id)
    {
        $course=Course::findorfail($id);
        $users=$course->users_take;
        foreach ($users as $user){
            $user['paid']=$user->pivot->paid;
            $user['discount_used']=$user->pivot->discount_used;
        }
//        return $users;
        return view('showstudents',compact('users',$users))->with('key','course');
    }
    public function pparticipation($id)
    {
        $pack=Pack::findorfail($id);
        $users=$pack->takes;
        foreach ($users as $user){
            $user['paid']=$user->pivot->paid;
            $user['discount_used']=$user->pivot->discount_used;
            $user['start']=$user->pivot->start;
            $user['end']=$user->pivot->end;
        }
//        return $users;
        return view('showstudents',compact('users',$users))->with('key','pack');
    }
}
