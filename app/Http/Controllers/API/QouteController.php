<?php

namespace App\Http\Controllers\API;

use App\qoute;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\LocalImage;

class QouteController extends Controller
{
      /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'ar_text' => 'required',
            'ar_style' => 'required',
            'en_text' => 'required',
            'en_style' => 'required',
            'image' => 'required|image|mimes:png',
            'image-nums' => 'required',

        ]);
        if($validate->fails())
        {
            return response()->json($validate->errors());
        }
        $qoute = new qoute;
        if($request->hasFile('image'))
        {
            $imageName = $request->image->getClientOriginalName();
            $image = Image::make($request->image->getRealPath());
            $image->save(public_path().'/images/qoute/'.$imageName );
            $qoute->path = $imageName;
        }
        $qoute['ar-text'] = $request['ar-text'];
        $qoute['ar-style'] = $request['ar-style'];
        $qoute['en-text'] = $request['en-text'];
        $qoute['en-style'] = $request['en-style'];
        $qoute['image-nums'] = $request['image-nums'];
        $qoute->user()->associate($this->guard()->user());
        $qoute->save();
        return response()->json(['statue' => 'data saved successfully'],200);

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }
   
    /**
     * Get app local images
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getLocalImages()
    {
        $images = LocalImage::all();
        
        $result = array();
        foreach($images as $image)
        {
            $data = (object)['path' => $image->path,'nums' => explode(',',$image->nums)];
            $result[] = $data;
        }
        return response()->json($result);
    }
    public function addLocalImage(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'image' => 'required',
            'nums' => 'required',

        ]);
        if($validate->fails())
        {
            return response()->json($validate->errors());
        }
        $newImage = new LocalImage;
        if($request->hasFile('image'))
        {
            $imageName = $request->image->getClientOriginalName();
            $imagePath = Image::make($request->image->getRealPath());
            $imagePath->save(public_path().'/images/localImages/'.$imageName );
            $newImage->path = $imageName;
        }
        $newImage->nums = $request['nums'];
        $newImage->save();
        return response()->json('Image saved successfully',200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'ar_text' => 'required',
            'ar_style' => 'required',
            'en_text' => 'required',
            'en_style' => 'required',
            'image' => 'required|image|mimes:png',
            'image-nums' => 'required',
            'qoute_ID' => 'required',

        ]);
        if($validate->fails())
        {
            return response()->json($validate->errors());
        }
        $qoute = qoute::find($request['qoute_ID']);
        if($request->hasFile('image'))
        {
            $image_path = public_path().'/images/qoute/'.$qoute->image;
            Storage::delete($image_path);
            $imageName = $request->image->getClientOriginalName();
            $resize = Image::make($request->image->getRealPath());
            $resize->save(public_path().'/images/qoute/'.$imageName );
            
            
            $qoute->path = $imageName;
        }
        $qoute['ar-text'] = $request['ar-text'];
        $qoute['ar-style'] = $request['ar-style'];
        $qoute['en-text'] = $request['en-text'];
        $qoute['en-style'] = $request['en-style'];
        $qoute['image-nums'] = $request['image-nums'];
        $qoute->user()->associate($this->guard()->user());
        $qoute->save();
        return response()->json(['statue' => 'data updated successfully'],200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function guard()
    {
        return Auth::guard('api');
    }
}
