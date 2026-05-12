<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\SoftDeletes;
    //Same namespace
    // use App\Models\Attendance;
    // use App\Models\Fee;
    // use App\Models\SchoolClass;
    // use App\Models\Grade;

    class Student extends Model
    {
        use SoftDeletes;

        protected $fillable = ['school_class_id', 'first_name', 'last_name', 'id', 'date_of_birth', 'gender','enrolment_id'];

        // Composition: Student belongs to a Class
        public function schoolClass(): BelongsTo
        {
            return $this->belongsTo(SchoolClass::class);
        }

        public function grades(): HasMany
        {
            return $this->hasMany(Grade::class);
        }

        public function attendance(): HasMany
        {
            return $this->hasMany(Attendance::class);
        }

        public function fees(): HasMany
        {
            return $this->hasMany(Fee::class);
        }

        //Total balance across all terms
        public function getTotalBalanceAttribute()
        {
            return $this->fees()->sum('balance');
        }
    }
?>