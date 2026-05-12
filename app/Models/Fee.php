<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $fillable = ['student_id', 'total_due', 'amount_paid', 'term', 'year'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    //Fee Status is computed, not stored
    public function getStatusAttribute(): string
    {
        if ($this->balance <= 0) return 'Cleared';
        if ($this->amount_paid > 0) return 'Partially Paid';
        return 'Pending';
    }
}
?>