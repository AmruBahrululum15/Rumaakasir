<?php
namespace App\Services;
use App\Models\CustomerAccount;

class CustomerIdentity
{
    public static function phone(?string $value):?string
    {
        if(!is_string($value) || !preg_match('/^[+0-9\s().-]+$/D',$value))return null;
        $number=preg_replace('/\D/','',$value);
        if(str_starts_with($number,'00'))$number=substr($number,2);
        if(str_starts_with($number,'0'))$number='62'.substr($number,1);
        return preg_match('/^[1-9][0-9]{7,14}$/D',$number)?$number:null;
    }
    public function phoneMatches(string $value,?int $ignore=null):array
    {
        $normalized=self::phone($value);if(!$normalized)return [];
        $ids=CustomerAccount::where('login_phone',$normalized)->when($ignore,fn($q)=>$q->whereKeyNot($ignore))->limit(2)->pluck('id')->all();
        if(count($ids)<2){
            // Legacy contacts remain untouched. Resolve cautiously until their
            // owner edits the phone through the validated customer profile.
            foreach(CustomerAccount::whereNull('login_phone')->when($ignore,fn($q)=>$q->whereKeyNot($ignore))->select('id','phone')->cursor() as $account){
                if(self::phone($account->phone)===$normalized)$ids[]=$account->id;
                if(count($ids)>=2)break;
            }
        }
        return $ids;
    }
    public function resolve(string $identifier):?CustomerAccount
    {
        $identifier=trim($identifier);
        if(filter_var($identifier,FILTER_VALIDATE_EMAIL)){
            $matches=CustomerAccount::whereRaw('LOWER(email) = ?',[strtolower($identifier)])->limit(2)->get();
            return $matches->count()===1?$matches->first():null;
        }
        $ids=$this->phoneMatches($identifier);
        return count($ids)===1?CustomerAccount::find($ids[0]):null;
    }
    public function phoneRule(?int $ignore=null):\Closure
    {
        return function($attribute,$value,$fail)use($ignore){
            if(!self::phone($value))$fail('Nomor HP tidak valid.');
            elseif($this->phoneMatches($value,$ignore))$fail('Nomor HP sudah digunakan akun customer lain. Gunakan nomor berbeda atau masuk dengan email.');
        };
    }
}
