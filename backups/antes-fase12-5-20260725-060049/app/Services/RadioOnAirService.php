<?php
namespace App\Services;use App\Models\RadioScheduleSlot;use App\Models\RadioStation;use Carbon\CarbonInterface;
class RadioOnAirService{
 public function current(RadioStation $station,?CarbonInterface $now=null):?RadioScheduleSlot{$now=$now?:now();return RadioScheduleSlot::with(['program.host'])->whereHas('program',fn($q)=>$q->where('radio_station_id',$station->id)->where('is_active',true))->where('is_active',true)->where('day_of_week',$now->dayOfWeek)->where('starts_at','<=',$now->format('H:i:s'))->where('ends_at','>',$now->format('H:i:s'))->first();}
}
