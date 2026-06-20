<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Message Templates
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['marketing', 'utility', 'authentication'])->default('marketing');
            $table->enum('message_type', ['text', 'interactive', 'media', 'template', 'flow'])->default('text');
            $table->string('header_type')->nullable(); // text, image, document, video, location
            $table->text('header_value')->nullable();
            $table->text('body');
            $table->text('footer')->nullable();
            $table->json('buttons')->nullable(); // [{type: 'quick_reply'|'cta_url'|'cta_phone'|'flow', title, payload}]
            $table->json('sections')->nullable(); // for list messages [{title, rows: [{id, title, description}]}]
            $table->string('media_url')->nullable();
            $table->string('catalog_id')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamps();
        });

        // WhatsApp Flows (interactive JSON forms)
        Schema::create('whatsapp_flows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('flow_id')->nullable(); // Meta Flow ID once deployed
            $table->json('flow_json'); // The JSON form definition
            $table->json('flow_data')->nullable(); // Pre-filled data
            $table->enum('status', ['draft', 'deployed', 'archived'])->default('draft');
            $table->timestamps();
        });

        // Multi-step Conversation Flows
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('trigger_event', ['lead_created', 'purchase_made', 'broadcast_reply', 'manual', 'schedule'])->default('manual');
            $table->json('steps'); // [{step_order, message_type, template_id, delay_minutes, conditions: [{field, operator, value, next_step}]}]
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Conversation Logs
        Schema::create('whatsapp_conversation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('contact_id');
            $table->integer('current_step')->default(0);
            $table->enum('status', ['active', 'completed', 'exited'])->default('active');
            $table->text('last_response')->nullable();
            $table->timestamp('last_step_at')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('whatsapp_conversations')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('whatsapp_contacts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversation_logs');
        Schema::dropIfExists('whatsapp_conversations');
        Schema::dropIfExists('whatsapp_flows');
        Schema::dropIfExists('whatsapp_templates');
    }
};
