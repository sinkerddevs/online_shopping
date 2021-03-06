<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Category;
use App\Banner;
use function GuzzleHttp\json_encode;

class IndexController extends Controller
{
    public function index(){
        //In ascending order by default
        $productsAll = product::get();

        //In ascending order 
        $productsAll = Product::orderBy('id','DESC')->get();

        //In Random Order
        $productsAll = product::inRandomOrder()->where('status',1)->where('feature_items',1)->Paginate(6);

        // Get all Categories and sub Categories 

        $categories = Category::with('categories')->where(['parent_id'=>0])->get();
        // $categories = json_decode(json_encode($categories));
        // echo "<pre>",print_r($categories);die;


        //basic category im html
       /* $categories_menu = "";
        foreach ($categories as $cat) {
            $categories_menu .=" <div class='panel-heading'>
                                <h4 class='panel-title'>
                                    <a data-toggle='collapse' data-parent='#accordian'
                                         href='#".$cat->id."'>
                                        <span class='badge pull-right'><i class='fa fa-plus'></i></span>
                                        ".$cat->name."
                                    </a>
                                </h4>
                            </div>
                           <div id='".$cat->id."' class='panel-collapse collapse'>
                                <div class='panel-body'>
                                    <ul>";
                                    $sub_categories = Category::where(['parent_id'=>$cat->id])
                                    ->get();
                                    foreach ($sub_categories as $subcat) {
                                        $categories_menu .="<li><a href='".$subcat->url."'>
                                        ".$subcat->name." </a></li>";        
                                    }                     
                                        $categories_menu .="</ul>
                                </div>
                             </div>
                            ";
            
        }*/
        //end
        //ແທັກ Meta

        $meta_title = "E-SMShopping Wesite";
        $meta_description = "Online Shopping site for Men, Women and kids Clothing";
        $meta_keyword = "E-SMShopping Website, Online Shopping";
        $banners = Banner::where('status','1')->get();
        return view('index')->with(compact('productsAll','categories_menu','categories','banners','meta_title','meta_description','meta_keyword'));
    }
}
