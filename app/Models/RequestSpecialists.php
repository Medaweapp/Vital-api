<?php

namespace App\Models;

use App\FcmHelper;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Class RequestSpecialists
 *
 * @package App\Models
 * @version February 19, 2020, 5:59 pm UTC
 * @property User user
 * @property User doctor
 * @property string name
 * @property string address
 * @property string start_time
 * @property string end_time
 * @property double price
 * @property number number_of_hour
 * @property double longitude
 * @property double latitude
 * @property integer user_id
 * @property integer doctor_id
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists newQuery()
 * @method static Builder|RequestSpecialists onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereNumberOfHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequestSpecialists whereUserId($value)
 * @method static Builder|RequestSpecialists withTrashed()
 * @method static Builder|RequestSpecialists withoutTrashed()
 */
class RequestSpecialists extends Model
{
    use SoftDeletes;

    public $table = 'request_specialists';


    protected $dates = ['deleted_at'];


    public $fillable = [
        'name',
        'address',
        'start_time',
        'end_time',
        'number_of_hour',
        'longitude',
        'latitude',
        'price',
        'user_id',
        'doctor_id',
        'medical_id'
    ];


    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'address' => 'string',
        'start_time' => 'date:Y-m-d',
        'end_time' => 'date:Y-m-d',
        'longitude' => 'double',
        'latitude' => 'double',
        'user_id' => 'integer',
        'doctor_id' => 'integer',
        'medical_id' => 'integer',
        'price' => 'float',
        'number_of_hour' => 'integer'
    ];

    /**
     * @param $value
     * @return string
     * @throws \Exception
     * //     */
//    public function setStartTimeAttribute($value)
//    {
//        return $this->attributes['start_time'] = (new Carbon($value))->format('Y-m-d');
//    }

    /**
     * @param $value
     * @return string
     * @throws \Exception
     */
//    public function setEndTimeAttribute($value)
//    {
//        return $this->attributes['end_time'] = (new Carbon($value))->format('Y-m-d');
//    }

    /**
     * @param $medical_id
     * @return bool
     */
    public function newRequestProcess($medical_id)
    {
        $user = Auth::user();
//        $employ = Employ::where('medical_field_id', $medical_id)->first()
//            ->whith('doctor')->get('fcm_registration_id');

        if ($user) {
            $wallet = Wallet::where('user_id', $user->id)->first();
            $wallet->balance = $wallet->balance - env('REQUEST_POINT');
            $wallet->save();

            $fcm = new FcmHelper();
//            $fcm->send_android_fcm_all($employ, 'New Request', "Doctor required");
        }
        return false;
    }

    /**
     * @return BelongsTo
     **/

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return BelongsTo
     **/
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'id');
    }

    /**
     * @return BelongsTo
     **/
    public function specialties()
    {
        return $this->belongsTo(MedicalSpecialty::class, 'medical_id', 'id');
    }

    /**
     * @return HasOne
     **/
    public function acceptRequest()
    {
        return $this->hasOne(AcceptRequestSpecialists::class, 'request_id', 'id');
    }

    /**
     * @param $medical_id
     * @return bool|string
     */
    public function users_notfication($medical_id, $requestId)
    {
        $medical = MedicalSpecialty::where('id', $medical_id)->first('name');
        $message = 'New Request';
        $title = "$medical->name required";
        $fcm_registration_id = array();
        $fcm_registration = User::all('fcm_registration_id');
        foreach ($fcm_registration as $device) {
            $fcm_registration_id[] = $device->fcm_registration_id;
        }
        $result = new FcmHelper();
        return $result->send_android_fcm_all($fcm_registration_id, $title, $message, $requestId);
    }

}
