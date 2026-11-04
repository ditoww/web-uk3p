<?Php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevAttachment extends Model
{
    use HasFactory;

    protected $table = 'devAttachments';
    protected $primaryKey = 'id';
    protected $fillable = ['chatID', 'fileName', 'fileExtension', 'created_at', 'updated_at'];

    public function devChat()
    {
        return $this->belongsTo(DevChat::class, 'chatID');
    }
}
