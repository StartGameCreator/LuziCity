<?php
namespace App\Http\Controllers;
use App\Models\AdvertiserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class AdminAdvertiserRelationshipController extends Controller
{
 private function guard():void{abort_unless(auth()->user()?->hasAnyRole(['Super Admin','Admin']),403);}
 public function contact(Request $r,AdvertiserProfile $advertiser):RedirectResponse{$this->guard();$d=$r->validate(['name'=>'required|string|max:140','position'=>'nullable|string|max:100','phone'=>'nullable|string|max:30','whatsapp'=>'nullable|string|max:30','email'=>'nullable|email|max:180','notes'=>'nullable|string|max:3000','is_primary'=>'nullable|boolean']);$d['is_primary']=$r->boolean('is_primary');$advertiser->contacts()->create($d);return back()->with('status','Contato adicionado.');}
 public function address(Request $r,AdvertiserProfile $advertiser):RedirectResponse{$this->guard();$d=$r->validate(['type'=>'required|in:commercial,billing,mailing','postal_code'=>'nullable|string|max:12','street'=>'nullable|string|max:180','number'=>'nullable|string|max:30','complement'=>'nullable|string|max:100','district'=>'nullable|string|max:100','city'=>'nullable|string|max:100','state'=>'nullable|string|size:2']);$advertiser->addresses()->create($d);return back()->with('status','Endereço adicionado.');}
 public function history(Request $r,AdvertiserProfile $advertiser):RedirectResponse{$this->guard();$d=$r->validate(['type'=>'required|in:meeting,call,email,visit,proposal,note','title'=>'required|string|max:180','description'=>'nullable|string|max:10000','occurred_at'=>'nullable|date']);$advertiser->histories()->create($d+['user_id'=>auth()->id(),'occurred_at'=>$d['occurred_at']??now()]);return back()->with('status','Histórico registrado.');}
 public function document(Request $r,AdvertiserProfile $advertiser):RedirectResponse{$this->guard();$d=$r->validate(['type'=>'required|in:contract,cnpj,logo,media,proposal,other','document'=>'required|file|mimes:pdf,png,jpg,jpeg,webp,doc,docx,xls,xlsx,mp4,mov|max:51200']);$f=$r->file('document');$path=$f->store('advertisers/'.$advertiser->id, 'local');$advertiser->documents()->create(['uploaded_by'=>auth()->id(),'type'=>$d['type'],'name'=>$f->getClientOriginalName(),'path'=>$path,'mime_type'=>$f->getMimeType(),'size_bytes'=>$f->getSize()]);return back()->with('status','Documento enviado.');}
}
