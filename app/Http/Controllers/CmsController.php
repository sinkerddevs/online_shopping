<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CmsPage;
use App\Category;
use Illuminate\Support\Facades\Mail;

class CmsController extends Controller
{
    public function addCmsPage(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            // echo "<pre>";print_r($data);die;
            $cmsPage = new CmsPage;
            $cmsPage->title = $data['title'];
            $cmsPage->url= $data['url'];
            $cmsPage->description = $data['description'];
            if(empty($data['status']=="on")){
                $status = 0;
            }else{
                $status = 1;
            }
            $cmsPage->status = $status;
            $cmsPage->save();
            return redirect(url('/admin/view-cms-pages'))->with('flash_message_success','ທ່ານເພີ່ມ CMS Page ຮຽບຮ້ອຍແລ້ວ!!');
        }
        return view('admin.pages.add_cms_Page');
    }
    public function viewCmsPages(){
        $cmsPages = CmsPage::get();
        // $cmsPage = json_decode(json_encode($cmsPage));
        // echo "<pre>";print_r($cmsPage);die;
        return view('admin.pages.view_cms_pages')->with(compact('cmsPages'));
    }
    public function editCmsPage(Request $request,$id=null){
        if($request->isMethod('post')){
            $data = $request->all();
            if(empty($data['status'])){
                $status = 0;
            }else{
                $status = 1;
            }
            if(empty($data['url'])){
                return redirect()->back()->with('flash_message_error','ກະລຸນາໃສ່ URL');
            }
            // echo "<pre>";print_r($data);die;
            CmsPage::where(['id'=>$id])->update(['title'=>$data['title'],'url'=>$data['url'],'description'=>$data['description'],
            'status'=>$status]);
            return redirect('/admin/view-cms-pages')->with('flash_message_success','ອັບເດດຮຽບຮ້ອຍແລ້ວ');
        }
        $PageDetails = Cmspage::where(['id'=>$id])->first();
        return view('admin.pages.edit_cms_page')->with(compact('PageDetails'));
    }
    public function deleteCmsPage($id=null){
        if(!empty($id)){
            CmsPage::where(['id'=>$id])->delete();
            return redirect('/admin/view-cms-pages')->with('flash_message_success','ລຶບ Cms Page ສຳເລັດແລ້ວ!!');
        }
    }
    public function cmsPage($url){

        //ກວດສອບວ່າໜ້ານັ້ນໃຫ້ໃຊ້ງານບໍ ຫຼື ມີຢູຸ່ບໍ
         $cmsPageCount = CmsPage::where(['url'=>$url,'status'=>1])->count();
         if($cmsPageCount>0){
            $cmsPageDetails = CmsPage::where('url',$url)->first();
            $cmsPageDetails = json_decode(json_encode($cmsPageDetails));
         }else{
            return redirect(url('.'))->with('flash_message_error','ຂໍອະໄພໜ້າທີ່ທ່ານຄົ້ົນຫາບໍ່ມີໃນເວັບໄຊ');
         }
         $categories = Category::with('categories')->where(['parent_id'=>0])->get();
         $categories = json_decode(json_encode($categories));
        //  echo "<pre>";print_r($categories);die;
         
        //  echo "<pre>";print_r($cmsPageDetails);die;
         return view('pages.cms_page')->with(compact('categories','cmsPageDetails'));
    }
    public function contact(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            // echo "<pre>";print_r($data);die;

            //ກຳນົດການປ້ອນ
            $validatedData = $request->validate([
                'name' => 'required|unique:posts|max:255',
                'email' => 'required',
            ]);

            //ສົ່ງອີເມວໃຫ້ຜູ່ບໍລິຫານເວັບ
            $email = "smshopping.info@gmail.com";
            $messageData = [
                'name'=>$data['name'],
                'email'=>$data['email'],
                'subject'=>$data['subject'],
                'comment'=>$data['message']
                ];
                Mail::send('emails.enquiry',$messageData,function($message)use($email){
                    $message->to($email)->subject('ການສອບຖາມຈາກ E-SMShopping Website');
                });
                return redirect()->back()->with('flash_message_success','ຂອບຂອບໃຈ ສໍາລັບຄວາມຄິດເຫັນຂອງທ່ານ!! ພວກເຮົາຈະຕິດຕໍ່ກັບໃນໄວໆນີ້');
        }
        $categories = Category::with('categories')->where(['parent_id'=>0])->get();
         $categories = json_decode(json_encode($categories));
        return view('pages.contact')->with(compact('categories'));
    }
}
