<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('whatsapp_conversation_logs');
        Schema::dropIfExists('whatsapp_conversations');
        Schema::dropIfExists('whatsapp_flows');
        Schema::dropIfExists('whatsapp_templates');
        Schema::dropIfExists('whatsapp_groups');
        Schema::dropIfExists('whatsapp_contacts');
        Schema::dropIfExists('whatsapp_broadcasts');

        \App\Models\Setting::where('key', 'like', 'whatsapp%')->delete();
    }

    public function down()
    {
        // Tables can be recreated via the original migrations if needed
    }
};
