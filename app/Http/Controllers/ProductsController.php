<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Auth;
use Session;
use Image;
use App\Category;
use App\Product;

class ProductsController extends Controller
{
    public function addProduct(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;
            $product = new Product;
            if(empty($data['category_id'])){
                return redirect()->back()->with('flash_message_error','ບໍ່ສາມາດເພີ່ມສິນຄ້າ ກາລຸນາລອງໃໝ່ອີກຄັ້ງ !!');
            }
            $product->category_id = $data['category_id'];
            $product->product_name = $data['product_name'];
            $product->product_code = $data['product_code'];
            $product->product_color = $data['product_color'];
            if(!empty($data['description'])){
                $product->description = $data['description'];
            }else{
                $product->description = '';
            }
            $product->price = $data['price'];
            // Upload Image
            if($request->hasFile('image')){
               $image_tmp = Input::file('image');
                if($image_tmp->isValid()){
                    $extension = $image_tmp->getClientOriginalExtension();
                    $filename = rand(111,99999).'.'.$extension;
                    $large_image_path = 'images/backend_images/products/large/'.$filename;
                    $medium_image_path = 'images/backend_images/products/medium/'.$filename;
                    $small_image_path = 'images/backend_images/products/small/'.$filename;
                    //Resize Images
                    Image::make($image_tmp)->save($large_image_path);
                    Image::make($image_tmp)->resize(600,600)->save($medium_image_path);
                    Image::make($image_tmp)->resize(300,300)->save($small_image_path);
                    //Store Image name in Products table
                    $product->image = $filename;
                }
            }
            $product->save();
            return redirect('/admin/view-products')->with('flash_message_success','ເພີ່ມສິນຄ້າຮຽບຮ້ອຍແລ້ວ!!');
        }
        // Categories dropdown start
        $categories = Category::where(['parent_id'=>0])->get();
        $categories_dropdown = "<option value='' selected disabled>Select</option>";
        foreach ($categories as $cat) {
            $categories_dropdown .= "<option value='".$cat->id."'>".$cat->name."</option>";
            $sub_categories = Category::where(['parent_id'=>$cat->id])->get();
            foreach ($sub_categories as $sub_cat) {
                $categories_dropdown .= "<option value='".$sub_cat->id."'>&nbsp;--&nbsp;".$sub_cat->name."</option>";
            }
        }
        // Categories dropdown end
        return view('admin.products.add_product')->with(compact('categories_dropdown'));
    }
    public function viewProducts(){
        $products = Product::get();
        $products = json_decode(json_encode($products ));
        foreach ($products as $key => $val) {
            $category_name = Category::where(['id'=>$val->category_id])->first();
            $products[$key]->category_name = $category_name->name;
        }
        return view('admin.products.view_products')->with(compact('products'));
    }

    public function editProduct(Request $request,$id= null){
        if($request->isMethod('post')){
           $data = $request->all();
           // Upload Image
           if($request->hasFile('image')){
            $image_tmp = Input::file('image');
             if($image_tmp->isValid()){
                 $extension = $image_tmp->getClientOriginalExtension();
                 $filename = rand(111,99999).'.'.$extension;
                 $large_image_path = 'images/backend_images/products/large/'.$filename;
                 $medium_image_path = 'images/backend_images/products/medium/'.$filename;
                 $small_image_path = 'images/backend_images/products/small/'.$filename;
                 //Resize Images
                 Image::make($image_tmp)->save($large_image_path);
                 Image::make($image_tmp)->resize(600,600)->save($medium_image_path);
                 Image::make($image_tmp)->resize(300,300)->save($small_image_path);
             }

         }else{
                $filename = $data['current_image'];
         }

         if(empty($data['description'])){
             $data['description'] = '';
         }
          // echo "<pre>";print_r($data);die;  test for show data
          Product::where(['id'=>$id])->update(['category_id'=>$data['category_id'],'product_name'=>$data['product_name'],'product_code'=>$data['product_code'],'product_color'=>$data['product_color'],'description'=>$data['description'],'price'=>$data['price'],'image'=>$filename]);
                 return redirect('/admin/view-products')->with('flash_message_success','ອັບເດດສີນຄ້າສຳເລັດແລ້ວ!!');
        }
        // show product in form
           // get Product detial
            $productDetails = Product::where(['id'=>$id])->first();
            // Categories dropdown start
                $categories = Category::where(['parent_id'=>0])->get();
                $categories_dropdown = "<option value='' selected disabled>Select</option>";
                foreach ($categories as $cat) {
                    if($cat->id == $productDetails->category_id){
                        $selected = "selected";
                    }else{
                        $selected = "";
                    }
                    $categories_dropdown .= "<option value='".$cat->id."' ".$selected.">".$cat->name."</option>";
                    $sub_categories = Category::where(['parent_id'=>$cat->id])->get();
                    foreach ($sub_categories as $sub_cat) {
                        if($sub_cat->id == $productDetails->category_id){
                            $selected = "selected";
                        }else{
                            $selected = "";
                        }
                        $categories_dropdown .= "<option value='".$sub_cat->id."' ".$selected.">&nbsp;--&nbsp;".$sub_cat->name."</option>";
                    }
                }
        // Categories dropdown end
            return view('admin.products.edit_products')->with(compact('productDetails','categories_dropdown'));
            //return redirect('/admin/view-product')->with('flash_message_success','ອັບເດດ ສິນຄ້າ ສຳເລັດແລ້ວ!!');       
    }

    public function deleteProductImage($id = null){
        Product :: where(['id'=>$id])->update(['image'=>'']);
        return redirect()->back()->with('flash_message_success','ຮູບພາບສິນຄ້າຖືກລຶບຮຽບຮ້ອຍແລ້ວ!!');
    }  
    public function deleteProduct($id = null){
        if(!empty($id)){
            Product::where(['id'=>$id])->delete();
            return redirect('/admin/view-products')->with('flash_message_success','ລຶບສິນຄ້າສຳເລັດແລ້ວ!!');
        }
    }
}
