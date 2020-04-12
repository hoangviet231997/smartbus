<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerAccount extends Model
{
  protected $table = "partner_account";
  protected $guarded = [];

  public function company()
  {
      return $this->belongsTo('App\Models\Company', 'company_id');
  }
}
