@extends('layouts.admin')

@section('title', 'Edit Funnel: ' . $funnel->name)

@section('content')
<form method="POST" action="/admin/marketing/funnels/{{ $funnel->id }}" id="funnelForm">
    @csrf
    @method('PUT')
    
    <div class="mb-6 flex items-center justify-between">
        <a href="/admin/marketing/funnels" class="text-blue-600 hover:text-blue-800">&larr; Back to Funnels</a>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Save Changes
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Funnel Details</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Funnel Name</label>
                        <input type="text" name="name" value="{{ $funnel->name }}" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Goal</label>
                        <select name="goal" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                            <option value="lead_capture" {{ $funnel->goal == 'lead_capture' ? 'selected' : '' }}>Lead Capture</option>
                            <option value="sale" {{ $funnel->goal == 'sale' ? 'selected' : '' }}>Make Sale</option>
                            <option value="webinar_signup" {{ $funnel->goal == 'webinar_signup' ? 'selected' : '' }}>Webinar Signup</option>
                            <option value="consultation" {{ $funnel->goal == 'consultation' ? 'selected' : '' }}>Get Consultation</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                    <textarea name="description" rows="2" class="w-full border border-slate-300 rounded-lg px-4 py-2">{{ $funnel->description }}</textarea>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-slate-800">Funnel Stages</h2>
                    <button type="button" onclick="addStage()" class="text-blue-600 hover:text-blue-700 text-sm">
                        <i class="fas fa-plus mr-1"></i>Add Stage
                    </button>
                </div>

                <div id="stagesContainer" class="space-y-4">
                    @php $stages = $funnel->stages ?? collect([]) @endphp
                    @if($stages->count() > 0)
                        @foreach($stages as $index => $stage)
                        <div class="stage-item border border-slate-200 rounded-lg p-4" data-index="{{ $index }}">
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                                    {{ $index + 1 }}
                                </div>
                                <input type="text" name="stages[{{ $index }}][name]" value="{{ $stage->name }}" placeholder="Stage name" class="flex-1 border border-slate-300 rounded-lg px-3 py-1">
                                <select name="stages[{{ $index }}][type]" class="border border-slate-300 rounded-lg px-3 py-1">
                                    <option value="landing" {{ $stage->type == 'landing' ? 'selected' : '' }}>Landing Page</option>
                                    <option value="email" {{ $stage->type == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="sales_page" {{ $stage->type == 'sales_page' ? 'selected' : '' }}>Sales Page</option>
                                    <option value="checkout" {{ $stage->type == 'checkout' ? 'selected' : '' }}>Checkout</option>
                                    <option value="upsell" {{ $stage->type == 'upsell' ? 'selected' : '' }}>Upsell</option>
                                    <option value="thank_you" {{ $stage->type == 'thank_you' ? 'selected' : '' }}>Thank You</option>
                                    <option value="delay" {{ $stage->type == 'delay' ? 'selected' : '' }}>Wait/Delay</option>
                                </select>
                                <button type="button" onclick="removeStage(this)" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="ml-12 grid grid-cols-2 gap-4">
                                <input type="text" name="stages[{{ $index }}][content]" value="{{ $stage->content['url'] ?? '' }}" placeholder="URL or content" class="border border-slate-300 rounded-lg px-3 py-1">
                                <input type="number" name="stages[{{ $index }}][delay_days]" value="{{ $stage->delay_days }}" placeholder="Delay (days)" class="border border-slate-300 rounded-lg px-3 py-1">
                            </div>
                            <div class="ml-12 mt-4 border-t border-slate-200 pt-4">
                                <button type="button" onclick="toggleConditions(this)" class="flex items-center gap-2 text-sm text-purple-600 hover:text-purple-800 font-medium">
                                    <i class="fas fa-code-branch"></i>
                                    <span>Conditional Logic</span>
                                    @php $stageConditions = $stage->conditions ?? []; @endphp
                                    @if(count($stageConditions) > 0)
                                    <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs">{{ count($stageConditions) }} condition(s)</span>
                                    @endif
                                </button>
                                <div class="conditions-container mt-3 hidden">
                                    <div class="flex items-center gap-2 mb-3">
                                        <label class="text-xs text-slate-500">Logic:</label>
                                        <select name="stages[{{ $index }}][condition_logic]" class="border border-slate-300 rounded px-2 py-1 text-sm">
                                            <option value="and" {{ ($stage->condition_logic ?? 'and') == 'and' ? 'selected' : '' }}>All conditions (AND)</option>
                                            <option value="or" {{ ($stage->condition_logic ?? '') == 'or' ? 'selected' : '' }}>Any condition (OR)</option>
                                        </select>
                                    </div>
                                    <div class="conditions-list space-y-2">
                                        @if(count($stageConditions) > 0)
                                            @foreach($stageConditions as $condIndex => $condition)
                                            <div class="condition-row flex items-center gap-2 bg-slate-50 rounded-lg p-2">
                                                <select name="stages[{{ $index }}][conditions][{{ $condIndex }}][field]" class="border border-slate-300 rounded px-2 py-1 text-sm">
                                                    <option value="email_opened" {{ ($condition['field'] ?? '') == 'email_opened' ? 'selected' : '' }}>Lead opened email</option>
                                                    <option value="email_clicked" {{ ($condition['field'] ?? '') == 'email_clicked' ? 'selected' : '' }}>Lead clicked link</option>
                                                    <option value="score_exceeded" {{ ($condition['field'] ?? '') == 'score_exceeded' ? 'selected' : '' }}>Lead score exceeds</option>
                                                    <option value="has_tag" {{ ($condition['field'] ?? '') == 'has_tag' ? 'selected' : '' }}>Lead has tag</option>
                                                    <option value="visited_page" {{ ($condition['field'] ?? '') == 'visited_page' ? 'selected' : '' }}>Visited page</option>
                                                    <option value="no_activity" {{ ($condition['field'] ?? '') == 'no_activity' ? 'selected' : '' }}>No activity for (days)</option>
                                                </select>
                                                <input type="text" name="stages[{{ $index }}][conditions][{{ $condIndex }}][value]" value="{{ $condition['value'] ?? '' }}" placeholder="Value" class="border border-slate-300 rounded px-2 py-1 text-sm w-32">
                                                <input type="number" name="stages[{{ $index }}][conditions][{{ $condIndex }}][days]" value="{{ $condition['days'] ?? '' }}" placeholder="Days" class="border border-slate-300 rounded px-2 py-1 text-sm w-20">
                                                <button type="button" onclick="removeCondition(this)" class="text-red-500 hover:text-red-700">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" onclick="addCondition(this, {{ $index }})" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-plus mr-1"></i>Add Condition
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-8 text-slate-500">
                            <p>No stages yet. Click "Add Stage" to build your funnel.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Email Automation Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Email Automation</h2>
                <p class="text-sm text-slate-500 mb-4">Automatically send emails when leads enter this funnel.</p>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="automation_enabled" {{ $funnel->automation_enabled ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
                        <span class="ml-2 text-sm text-slate-700">Enable Email Automation</span>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Welcome Email Sequence</label>
                        <select name="welcome_sequence_id" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                            <option value="">-- Select Sequence --</option>
                            @if(isset($sequences))
                            @foreach($sequences as $seq)
                            <option value="{{ $seq->id }}" {{ $funnel->welcome_sequence_id == $seq->id ? 'selected' : '' }}>
                                {{ $seq->name }} ({{ $seq->steps_count ?? 0 }} emails)
                            </option>
                            @endforeach
                            @endif
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Sends immediately when someone enters the funnel</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Follow-up Sequence (After purchase)</label>
                        <select name="followup_sequence_id" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                            <option value="">-- Select Sequence --</option>
                            @if(isset($sequences))
                            @foreach($sequences as $seq)
                            <option value="{{ $seq->id }}" {{ $funnel->followup_sequence_id == $seq->id ? 'selected' : '' }}>
                                {{ $seq->name }} ({{ $seq->steps_count ?? 0 }} emails)
                            </option>
                            @endforeach
                            @endif
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Sends after a purchase is made</p>
                    </div>
                </div>
            </div>

            <!-- Visual Automation Builder -->
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-indigo-800">Lead Nurturing Automation</h2>
                        <p class="text-sm text-indigo-600">Build custom automation paths with wait triggers</p>
                    </div>
                    <button type="button" onclick="toggleAutomationBuilder()" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-indigo-700">
                        <i class="fas fa-cog mr-1"></i>Open Builder
                    </button>
                </div>

                <div id="automationBuilder" class="hidden">
                    <div class="bg-slate-900 rounded-lg p-4 mb-4" style="min-height: 400px;">
                        <div class="flex items-center justify-between mb-4 border-b border-slate-700 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400 text-sm">Drag nodes to build your automation:</span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="addAutomationNode('trigger')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-2 py-1 rounded text-xs">
                                    <i class="fas fa-bolt mr-1"></i>Trigger
                                </button>
                                <button type="button" onclick="addAutomationNode('email')" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs">
                                    <i class="fas fa-envelope mr-1"></i>Email
                                </button>
                                <button type="button" onclick="addAutomationNode('wait')" class="bg-amber-600 hover:bg-amber-700 text-white px-2 py-1 rounded text-xs">
                                    <i class="fas fa-clock mr-1"></i>Wait
                                </button>
                                <button type="button" onclick="addAutomationNode('condition')" class="bg-purple-600 hover:bg-purple-700 text-white px-2 py-1 rounded text-xs">
                                    <i class="fas fa-code-branch mr-1"></i>Condition
                                </button>
                            </div>
                        </div>
                        
                        <div id="automationCanvas" class="flex items-start gap-4 overflow-x-auto pb-4" style="min-height: 300px; align-items: flex-start;">
                            @php 
                            $workflows = $funnel->automation_workflows ?? [];
                            @endphp
                            @if(count($workflows) > 0)
                                @foreach($workflows as $idx => $node)
                                <div class="automation-node bg-slate-800 rounded-lg p-3 min-w-[180px] border border-slate-600 node-{{ $node['type'] }}" data-index="{{ $idx }}">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold uppercase {{ $node['type'] == 'trigger' ? 'text-emerald-400' : ($node['type'] == 'email' ? 'text-blue-400' : ($node['type'] == 'wait' ? 'text-amber-400' : 'text-purple-400')) }}">
                                            @if($node['type'] == 'trigger')<i class="fas fa-bolt mr-1"></i>@endif
                                            @if($node['type'] == 'email')<i class="fas fa-envelope mr-1"></i>@endif
                                            @if($node['type'] == 'wait')<i class="fas fa-clock mr-1"></i>@endif
                                            @if($node['type'] == 'condition')<i class="fas fa-code-branch mr-1"></i>@endif
                                            {{ $node['type'] }}
                                        </span>
                                        <button type="button" onclick="removeNode(this)" class="text-slate-500 hover:text-red-400">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    @if($node['type'] == 'trigger')
                                    <select name="automation_workflows[{{ $idx }}][trigger_type]" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mb-2">
                                        <option value="lead_enters" {{ ($node['trigger_type'] ?? '') == 'lead_enters' ? 'selected' : '' }}>Lead enters funnel</option>
                                        <option value="lead_converts" {{ ($node['trigger_type'] ?? '') == 'lead_converts' ? 'selected' : '' }}>Lead converts</option>
                                        <option value="purchases" {{ ($node['trigger_type'] ?? '') == 'purchases' ? 'selected' : '' }}>Makes purchase</option>
                                        <option value="inactive_days" {{ ($node['trigger_type'] ?? '') == 'inactive_days' ? 'selected' : '' }}>Inactive for X days</option>
                                    </select>
                                    <input type="number" name="automation_workflows[{{ $idx }}][days]" value="{{ $node['days'] ?? '' }}" placeholder="Days" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1">
                                    @elseif($node['type'] == 'email')
                                    <select name="automation_workflows[{{ $idx }}][sequence_id]" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mb-2">
                                        <option value="">Select Sequence</option>
                                        @if(isset($sequences))
                                        @foreach($sequences as $seq)
                                        <option value="{{ $seq->id }}" {{ ($node['sequence_id'] ?? '') == $seq->id ? 'selected' : '' }}>{{ $seq->name }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    @elseif($node['type'] == 'wait')
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="number" name="automation_workflows[{{ $idx }}][wait_days]" value="{{ $node['wait_days'] ?? 1 }}" class="bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1" min="0">
                                        <select name="automation_workflows[{{ $idx }}][wait_unit]" class="bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1">
                                            <option value="days" {{ ($node['wait_unit'] ?? '') == 'days' ? 'selected' : '' }}>Days</option>
                                            <option value="hours" {{ ($node['wait_unit'] ?? '') == 'hours' ? 'selected' : '' }}>Hours</option>
                                            <option value="weeks" {{ ($node['wait_unit'] ?? '') == 'weeks' ? 'selected' : '' }}>Weeks</option>
                                        </select>
                                    </div>
                                    <div class="mt-2">
                                        <label class="text-xs text-slate-400">Specific datetime:</label>
                                        <input type="datetime-local" name="automation_workflows[{{ $idx }}][wait_until]" value="{{ $node['wait_until'] ?? '' }}" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mt-1">
                                    </div>
                                    <div class="mt-2">
                                        <label class="text-xs text-slate-400">Or day of week:</label>
                                        <select name="automation_workflows[{{ $idx }}][wait_day]" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mt-1">
                                            <option value="">Any day</option>
                                            <option value="monday" {{ ($node['wait_day'] ?? '') == 'monday' ? 'selected' : '' }}>Monday</option>
                                            <option value="tuesday" {{ ($node['wait_day'] ?? '') == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                                            <option value="wednesday" {{ ($node['wait_day'] ?? '') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                            <option value="thursday" {{ ($node['wait_day'] ?? '') == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                            <option value="friday" {{ ($node['wait_day'] ?? '') == 'friday' ? 'selected' : '' }}>Friday</option>
                                            <option value="saturday" {{ ($node['wait_day'] ?? '') == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                            <option value="sunday" {{ ($node['wait_day'] ?? '') == 'sunday' ? 'selected' : '' }}>Sunday</option>
                                        </select>
                                    </div>
                                    @elseif($node['type'] == 'condition')
                                    <select name="automation_workflows[{{ $idx }}][condition_field]" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mb-2">
                                        <option value="score" {{ ($node['condition_field'] ?? '') == 'score' ? 'selected' : '' }}>Lead Score</option>
                                        <option value="has_purchased" {{ ($node['condition_field'] ?? '') == 'has_purchased' ? 'selected' : '' }}>Has Purchased</option>
                                        <option value="email_opens" {{ ($node['condition_field'] ?? '') == 'email_opens' ? 'selected' : '' }}>Email Opens</option>
                                        <option value="tag" {{ ($node['condition_field'] ?? '') == 'tag' ? 'selected' : '' }}>Has Tag</option>
                                    </select>
                                    <select name="automation_workflows[{{ $idx }}][condition_operator]" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mb-2">
                                        <option value="greater_than" {{ ($node['condition_operator'] ?? '') == 'greater_than' ? 'selected' : '' }}>Greater than</option>
                                        <option value="equals" {{ ($node['condition_operator'] ?? '') == 'equals' ? 'selected' : '' }}>Equals</option>
                                        <option value="less_than" {{ ($node['condition_operator'] ?? '') == 'less_than' ? 'selected' : '' }}>Less than</option>
                                    </select>
                                    <input type="text" name="automation_workflows[{{ $idx }}][condition_value]" value="{{ $node['condition_value'] ?? '' }}" placeholder="Value" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1">
                                    @endif
                                    <input type="hidden" name="automation_workflows[{{ $idx }}][type]" value="{{ $node['type'] }}">
                                </div>
                                @if($idx < count($workflows) - 1)
                                <div class="flex items-center justify-center text-slate-500 mt-8">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                                @endif
                                @endforeach
                            @else
                            <div class="text-center text-slate-500 w-full py-12">
                                <i class="fas fa-project-diagram text-4xl mb-4"></i>
                                <p>Click buttons above to add automation nodes</p>
                                <p class="text-xs mt-2">Start with a Trigger, then add Wait/Email nodes</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-indigo-50 rounded-lg p-4">
                        <h4 class="font-bold text-indigo-800 mb-2">Node Types Guide:</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-emerald-500 rounded-full"></span>
                                <span class="text-indigo-700">Trigger - Starts automation</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                                <span class="text-indigo-700">Email - Send sequence</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-amber-500 rounded-full"></span>
                                <span class="text-indigo-700">Wait - Delay with triggers</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-purple-500 rounded-full"></span>
                                <span class="text-indigo-700">Condition - Branch logic</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- One-Click Upsell -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-green-800 mb-4">One-Click Upsell</h2>
                <p class="text-sm text-green-600 mb-4">Offer an additional product right after purchase.</p>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="upsell_enabled" {{ $funnel->upsell_enabled ? 'checked' : '' }} class="rounded border-slate-300 text-green-600">
                        <span class="ml-2 text-sm text-slate-700">Enable Upsell</span>
                    </label>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Upsell Product</label>
                        <select name="upsell_product_id" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                            <option value="">-- Select Product --</option>
                            @if(isset($products))
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ $funnel->upsell_product_id == $product->id ? 'selected' : '' }}>
                                {{ $product->title }} - N{{ number_format($product->price) }}
                            </option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Discount %</label>
                        <input type="number" name="upsell_discount" value="{{ $funnel->upsell_discount ?? 0 }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" min="0" max="100">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Button Text</label>
                    <input type="text" name="upsell_button_text" value="{{ $funnel->upsell_button_text ?? 'Add to Order' }}" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Timer (seconds before showing)</label>
                    <input type="number" name="upsell_timer" value="{{ $funnel->upsell_timer ?? 30 }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" min="0">
                    <p class="text-xs text-slate-500 mt-1">0 = show immediately</p>
                </div>
            </div>
            
            <!-- Order Bumps -->
            <div class="bg-gradient-to-r from-rose-50 to-pink-50 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-rose-800">Order Bumps</h2>
                        <p class="text-sm text-rose-600">Add one-click upsells on checkout page</p>
                    </div>
                    <button type="button" onclick="addOrderBump()" class="bg-rose-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-rose-700">
                        <i class="fas fa-plus mr-1"></i>Add Bump
                    </button>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="order_bumps_enabled" {{ $funnel->order_bumps_enabled ? 'checked' : '' }} class="rounded border-slate-300 text-rose-600">
                        <span class="ml-2 text-sm text-slate-700">Enable Order Bumps</span>
                    </label>
                </div>
                
                <div id="orderBumpsContainer" class="space-y-4">
                    @php $orderBumps = $funnel->order_bumps ?? [] @endphp
                    @if(count($orderBumps) > 0)
                        @foreach($orderBumps as $index => $bump)
                        <div class="bump-item border border-rose-200 rounded-lg p-4" data-index="{{ $index }}">
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-8 h-8 bg-rose-600 text-white rounded-full flex items-center justify-center font-bold">
                                    {{ $index + 1 }}
                                </div>
                                <input type="text" name="order_bumps[{{ $index }}][title]" value="{{ $bump['title'] ?? '' }}" placeholder="Bump title" class="flex-1 border border-slate-300 rounded-lg px-3 py-1">
                                <select name="order_bumps[{{ $index }}][product_id]" class="border border-slate-300 rounded-lg px-3 py-1">
                                    <option value="">Select Product</option>
                                    @if(isset($products))
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ ($bump['product_id'] ?? '') == $product->id ? 'selected' : '' }}>
                                        {{ $product->title }}
                                    </option>
                                    @endforeach
                                    @endif
                                </select>
                                <button type="button" onclick="removeOrderBump(this)" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="ml-12 grid grid-cols-2 gap-4">
                                <input type="text" name="order_bumps[{{ $index }}][description]" value="{{ $bump['description'] ?? '' }}" placeholder="Quick description" class="border border-slate-300 rounded-lg px-3 py-1">
                                <input type="number" name="order_bumps[{{ $index }}][discount]" value="{{ $bump['discount'] ?? 0 }}" placeholder="Discount %" class="border border-slate-300 rounded-lg px-3 py-1">
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-sm text-slate-500 text-center py-4">No order bumps yet. Click "Add Bump" to add one.</p>
                    @endif
                </div>
            </div>
            
            <!-- Custom Thank You Page -->
            <div class="bg-gradient-to-r from-teal-50 to-cyan-50 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-teal-800 mb-4">Custom Thank You Page</h2>
                <p class="text-sm text-teal-600 mb-4">Customize what customers see after purchase.</p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Page Title</label>
                    <input type="text" name="thank_you_title" value="{{ $funnel->thank_you_title ?? 'Thank You for Your Order!' }}" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Message</label>
                    <textarea name="thank_you_message" rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2">{{ $funnel->thank_you_message ?? 'Your order has been confirmed. We will send you a confirmation email shortly.' }}</textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Video URL (optional)</label>
                    <input type="url" name="thank_you_video" value="{{ $funnel->thank_you_video }}" placeholder="https://youtube.com/..." class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    <p class="text-xs text-slate-500 mt-1">Embed a welcome video on the thank you page</p>
                </div>
            </div>
            
            <!-- Exit Intent Popup -->
            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-amber-800 mb-4">Exit Intent Popup</h2>
                <p class="text-sm text-amber-600 mb-4">Show a popup when visitors are about to leave.</p>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="exit_popup_enabled" {{ $funnel->exit_popup_enabled ? 'checked' : '' }} class="rounded border-slate-300 text-amber-600">
                        <span class="ml-2 text-sm text-slate-700">Enable Exit Popup</span>
                    </label>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Special Offer</label>
                    <input type="text" name="exit_popup_offer" value="{{ $funnel->exit_popup_offer ?? 'Get 10% off if you order today!' }}" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Discount %</label>
                    <input type="number" name="exit_popup_discount" value="{{ $funnel->exit_popup_discount ?? 10 }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" min="0" max="100">
                </div>
            </div>
            
            <!-- Refund Policy -->
            <div class="bg-gradient-to-r from-red-50 to-orange-50 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-red-800 mb-4">Refund Policy</h2>
                <p class="text-sm text-red-600 mb-4">Manage customer refunds and guarantees.</p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Refund Policy Text</label>
                    <select name="refund_policy" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                        <option value="none" {{ $funnel->refund_policy == 'none' ? 'selected' : '' }}>No Refunds</option>
                        <option value="days" {{ $funnel->refund_policy == 'days' ? 'selected' : '' }}>Refund within X days</option>
                        <option value="lifetime" {{ $funnel->refund_policy == 'lifetime' ? 'selected' : '' }}>Lifetime Guarantee</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Refund Period (days)</label>
                    <input type="number" name="refund_period_days" value="{{ $funnel->refund_period_days ?? 30 }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" min="0">
                    <p class="text-xs text-slate-500 mt-1">0 = no refunds allowed</p>
                </div>
            </div>
            
            <!-- Affiliate Tracking -->
            <div class="bg-gradient-to-r from-violet-50 to-purple-50 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-violet-800 mb-4">Affiliate Program</h2>
                <p class="text-sm text-violet-600 mb-4">Let affiliates promote your funnel for commissions.</p>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="affiliate_enabled" {{ $funnel->affiliate_enabled ? 'checked' : '' }} class="rounded border-slate-300 text-violet-600">
                        <span class="ml-2 text-sm text-slate-700">Enable Affiliate Program</span>
                    </label>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Commission %</label>
                    <input type="number" name="affiliate_commission" value="{{ $funnel->affiliate_commission ?? 20 }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" min="0" max="100">
                    <p class="text-xs text-slate-500 mt-1">Percentage affiliates earn on referred sales</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Cookie Duration (days)</label>
                    <input type="number" name="affiliate_cookie_days" value="{{ $funnel->affiliate_cookie_days ?? 30 }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" min="1">
                    <p class="text-xs text-slate-500 mt-1">How long affiliate cookie lasts</p>
                </div>
            </div>
            
            <!-- Lead Scoring -->
            <div class="bg-gradient-to-r from-cyan-50 to-blue-50 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-cyan-800 mb-4">Lead Scoring</h2>
                <p class="text-sm text-cyan-600 mb-4">Score leads based on their engagement.</p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Points per page view</label>
                    <input type="number" name="score_per_page" value="{{ $funnel->score_per_page ?? 5 }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" min="0">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Points per email open</label>
                    <input type="number" name="score_per_email" value="{{ $funnel->score_per_email ?? 10 }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" min="0">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Points per checkout start</label>
                    <input type="number" name="score_per_checkout" value="{{ $funnel->score_per_checkout ?? 20 }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" min="0">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Hot lead threshold</label>
                    <input type="number" name="score_hot_threshold" value="{{ $funnel->score_hot_threshold ?? 100 }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" min="0">
                    <p class="text-xs text-slate-500 mt-1">Score above which leads are marked as hot</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Auto-tag hot leads</label>
                    <input type="text" name="hot_lead_tag" value="{{ $funnel->hot_lead_tag ?? '' }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" placeholder="e.g., hot-lead, ready-to-buy">
                    <p class="text-xs text-slate-500 mt-1">Tag to add when leads become hot (comma-separated)</p>
                </div>
            </div>
            
            <!-- Pixel Tracking -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-blue-800 mb-4">Retargeting Pixels</h2>
                <p class="text-sm text-blue-600 mb-4">Track visitors and run ads to people who visited your funnel.</p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Facebook Pixel ID</label>
                    <input type="text" name="facebook_pixel" value="{{ $funnel->facebook_pixel }}" placeholder="1234567890" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Google Analytics ID</label>
                    <input type="text" name="google_pixel" value="{{ $funnel->google_pixel }}" placeholder="G-XXXXXXXXXX" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
            </div>
            
            <!-- Countdown Timer -->
            <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-orange-800 mb-4">Scarcity Timer</h2>
                <p class="text-sm text-orange-600 mb-4">Add urgency to increase conversions.</p>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="countdown_enabled" {{ $funnel->countdown_enabled ? 'checked' : '' }} class="rounded border-slate-300 text-orange-600">
                        <span class="ml-2 text-sm text-slate-700">Enable Countdown Timer</span>
                    </label>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Timer Duration (hours)</label>
                    <input type="number" name="countdown_hours" value="{{ $funnel->countdown_hours ?? 24 }}" class="w-full border border-slate-300 rounded-lg px-4 py-2" min="1" max="168">
                    <p class="text-xs text-slate-500 mt-1">Shows countdown from this many hours</p>
                </div>
            </div>

            <!-- Webhook Alerts -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-purple-800 mb-4">Webhook Alerts</h2>
                <p class="text-sm text-purple-600 mb-4">Get instant notifications when someone converts.</p>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="webhook_enabled" {{ $funnel->webhook_enabled ? 'checked' : '' }} class="rounded border-slate-300 text-purple-600">
                        <span class="ml-2 text-sm text-slate-700">Enable Webhook</span>
                    </label>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Webhook URL</label>
                    <input type="url" name="webhook_url" value="{{ $funnel->webhook_url }}" placeholder="https://hooks.slack.com/services/..." class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    <p class="text-xs text-slate-500 mt-1">Works with Slack, Zapier, Make, etc.</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Or Email Notification</label>
                    <input type="email" name="notify_email" value="{{ $funnel->notify_email }}" placeholder="your@email.com" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    <p class="text-xs text-slate-500 mt-1">Receive email when someone converts</p>
                </div>
                
                @if($funnel->webhook_enabled || $funnel->notify_email)
                <div class="mt-4 p-3 bg-purple-100 rounded-lg">
                    <p class="text-sm text-purple-700">
                        <i class="fas fa-bell mr-1"></i>
                        Alerts are active!
                    </p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Preview</h2>
                <div class="text-center py-4">
                    <div class="text-4xl text-slate-300 mb-2">
                        <i class="fas fa-funnel-dollar"></i>
                    </div>
                    <p class="text-sm text-slate-500">{{ $stages->count() ?? 0 }} stages</p>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-6">
                <h3 class="font-bold text-green-800 mb-2">Funnel Status</h3>
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-3 h-3 rounded-full {{ $funnel->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                    <span class="text-sm text-green-700">{{ $funnel->is_active ? 'Live' : 'Draft' }}</span>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Environment</label>
                    <select name="environment" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                        <option value="staging" {{ ($funnel->environment ?? 'staging') == 'staging' ? 'selected' : '' }}>Staging</option>
                        <option value="production" {{ ($funnel->environment ?? '') == 'production' ? 'selected' : '' }}>Production</option>
                    </select>
                </div>

                @if($funnel->deployed_at)
                <div class="text-xs text-slate-500 mb-3">
                    <i class="fas fa-clock mr-1"></i>
                    Last deployed: {{ $funnel->deployed_at->diffForHumans() }}
                </div>
                @endif
            </div>

            @if(($funnel->environment ?? 'staging') === 'staging')
            <div class="bg-indigo-50 rounded-lg p-6">
                <h3 class="font-bold text-indigo-800 mb-2">Deployment</h3>
                <p class="text-sm text-indigo-600 mb-4">Deploy this funnel to production</p>
                
                <div class="space-y-2">
                    <a href="/admin/marketing/funnels/{{ $funnel->id }}/deploy" 
                       class="block w-full bg-indigo-600 text-white text-center px-4 py-2 rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-rocket mr-2"></i>Deploy to Production
                    </a>
                    <a href="/admin/marketing/funnels/{{ $funnel->id }}/export" 
                       class="block w-full bg-slate-600 text-white text-center px-4 py-2 rounded-lg hover:bg-slate-700">
                        <i class="fas fa-download mr-2"></i>Export JSON
                    </a>
                </div>
            </div>
            @else
            <div class="bg-green-100 rounded-lg p-6">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="font-bold text-green-800">Live in Production</span>
                </div>
                <p class="text-sm text-green-700">This funnel is running in production mode.</p>
            </div>
            @endif

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-slate-800 mb-4">Import Funnel</h3>
                <form action="/admin/marketing/funnels/import" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="json_file" accept=".json" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mb-4">
                    <input type="hidden" name="mode" value="new">
                    <button type="submit" class="w-full bg-slate-600 text-white px-4 py-2 rounded-lg hover:bg-slate-700">
                        <i class="fas fa-upload mr-2"></i>Import JSON
                    </button>
                </form>
            </div>
        </div>
    </div>
</form>

<script>
let stageCount = {{ $stages->count() }};

function addStage() {
    const container = document.getElementById('stagesContainer');
    const html = `
        <div class="stage-item border border-slate-200 rounded-lg p-4" data-index="${stageCount}">
            <div class="flex items-center gap-4 mb-3">
                <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">${stageCount + 1}</div>
                <input type="text" name="stages[${stageCount}][name]" placeholder="Stage name" class="flex-1 border border-slate-300 rounded-lg px-3 py-1">
                <select name="stages[${stageCount}][type]" class="border border-slate-300 rounded-lg px-3 py-1">
                    <option value="landing">Landing Page</option>
                    <option value="email">Email</option>
                    <option value="sales_page">Sales Page</option>
                    <option value="checkout">Checkout</option>
                    <option value="upsell">Upsell</option>
                    <option value="thank_you">Thank You</option>
                    <option value="delay">Wait/Delay</option>
                </select>
                <button type="button" onclick="removeStage(this)" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="ml-12 grid grid-cols-2 gap-4">
                <input type="text" name="stages[${stageCount}][content]" placeholder="URL or content" class="border border-slate-300 rounded-lg px-3 py-1">
                <input type="number" name="stages[${stageCount}][delay_days]" placeholder="Delay (days)" class="border border-slate-300 rounded-lg px-3 py-1">
            </div>
            <div class="ml-12 mt-4 border-t border-slate-200 pt-4">
                <button type="button" onclick="toggleConditions(this)" class="flex items-center gap-2 text-sm text-purple-600 hover:text-purple-800 font-medium">
                    <i class="fas fa-code-branch"></i>
                    <span>Conditional Logic</span>
                </button>
                <div class="conditions-container mt-3 hidden">
                    <div class="flex items-center gap-2 mb-3">
                        <label class="text-xs text-slate-500">Logic:</label>
                        <select name="stages[${stageCount}][condition_logic]" class="border border-slate-300 rounded px-2 py-1 text-sm">
                            <option value="and">All conditions (AND)</option>
                            <option value="or">Any condition (OR)</option>
                        </select>
                    </div>
                    <div class="conditions-list space-y-2"></div>
                    <button type="button" onclick="addCondition(this, ${stageCount})" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-plus mr-1"></i>Add Condition
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    stageCount++;
}

function removeStage(btn) {
    const stage = btn.closest('.stage-item');
    if (document.querySelectorAll('.stage-item').length > 1) {
        stage.remove();
    }
}

let bumpCount = {{ count($orderBumps ?? []) }};

function addOrderBump() {
    const container = document.getElementById('orderBumpsContainer');
    const productOptions = `@if(isset($products))
@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->title }}</option>
@endforeach
@endif`;
    
    const html = `
        <div class="bump-item border border-rose-200 rounded-lg p-4" data-index="${bumpCount}">
            <div class="flex items-center gap-4 mb-3">
                <div class="w-8 h-8 bg-rose-600 text-white rounded-full flex items-center justify-center font-bold">${bumpCount + 1}</div>
                <input type="text" name="order_bumps[${bumpCount}][title]" placeholder="Bump title" class="flex-1 border border-slate-300 rounded-lg px-3 py-1">
                <select name="order_bumps[${bumpCount}][product_id]" class="border border-slate-300 rounded-lg px-3 py-1">
                    <option value="">Select Product</option>
                    ${productOptions}
                </select>
                <button type="button" onclick="removeOrderBump(this)" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="ml-12 grid grid-cols-2 gap-4">
                <input type="text" name="order_bumps[${bumpCount}][description]" placeholder="Quick description" class="border border-slate-300 rounded-lg px-3 py-1">
                <input type="number" name="order_bumps[${bumpCount}][discount]" placeholder="Discount %" class="border border-slate-300 rounded-lg px-3 py-1">
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    bumpCount++;
}

function removeOrderBump(btn) {
    const bump = btn.closest('.bump-item');
    bump.remove();
}

let nodeCount = {{ count($funnel->automation_workflows ?? []) }};
const sequencesList = @json($sequences ?? []);

function toggleAutomationBuilder() {
    const builder = document.getElementById('automationBuilder');
    builder.classList.toggle('hidden');
}

function addAutomationNode(type) {
    const canvas = document.getElementById('automationCanvas');
    const existingNodes = canvas.querySelectorAll('.automation-node');
    const nodeIndex = existingNodes.length;
    
    const nodeHtml = `
        <div class="automation-node bg-slate-800 rounded-lg p-3 min-w-[180px] border border-slate-600 node-${type}" data-index="${nodeIndex}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase ${type === 'trigger' ? 'text-emerald-400' : (type === 'email' ? 'text-blue-400' : (type === 'wait' ? 'text-amber-400' : 'text-purple-400'))}">
                    ${type === 'trigger' ? '<i class="fas fa-bolt mr-1"></i>' : ''}
                    ${type === 'email' ? '<i class="fas fa-envelope mr-1"></i>' : ''}
                    ${type === 'wait' ? '<i class="fas fa-clock mr-1"></i>' : ''}
                    ${type === 'condition' ? '<i class="fas fa-code-branch mr-1"></i>' : ''}
                    ${type}
                </span>
                <button type="button" onclick="removeNode(this)" class="text-slate-500 hover:text-red-400">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            ${getNodeFields(type, nodeIndex)}
            <input type="hidden" name="automation_workflows[${nodeIndex}][type]" value="${type}">
        </div>
    `;
    
    const connector = canvas.querySelector('.text-slate-500');
    if (connector) {
        connector.insertAdjacentHTML('beforebegin', nodeHtml);
    } else if (canvas.querySelector('.text-center')) {
        canvas.innerHTML = nodeHtml;
    } else {
        canvas.insertAdjacentHTML('beforeend', nodeHtml);
    }
    
    nodeCount++;
}

function getNodeFields(type, index) {
    let options = '<option value="">Select Sequence</option>';
    sequencesList.forEach(seq => {
        options += `<option value="${seq.id}">${seq.name}</option>`;
    });
    
    if (type === 'trigger') {
        return `
            <select name="automation_workflows[${index}][trigger_type]" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mb-2">
                <option value="lead_enters">Lead enters funnel</option>
                <option value="lead_converts">Lead converts</option>
                <option value="purchases">Makes purchase</option>
                <option value="inactive_days">Inactive for X days</option>
            </select>
            <input type="number" name="automation_workflows[${index}][days]" placeholder="Days" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1">
        `;
    } else if (type === 'email') {
        return `
            <select name="automation_workflows[${index}][sequence_id]" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mb-2">
                ${options}
            </select>
        `;
    } else if (type === 'wait') {
        return `
            <div class="grid grid-cols-2 gap-2">
                <input type="number" name="automation_workflows[${index}][wait_days]" value="1" class="bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1" min="0">
                <select name="automation_workflows[${index}][wait_unit]" class="bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1">
                    <option value="days">Days</option>
                    <option value="hours">Hours</option>
                    <option value="weeks">Weeks</option>
                </select>
            </div>
            <div class="mt-2">
                <label class="text-xs text-slate-400">Specific datetime:</label>
                <input type="datetime-local" name="automation_workflows[${index}][wait_until]" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mt-1">
            </div>
            <div class="mt-2">
                <label class="text-xs text-slate-400">Or day of week:</label>
                <select name="automation_workflows[${index}][wait_day]" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mt-1">
                    <option value="">Any day</option>
                    <option value="monday">Monday</option>
                    <option value="tuesday">Tuesday</option>
                    <option value="wednesday">Wednesday</option>
                    <option value="thursday">Thursday</option>
                    <option value="friday">Friday</option>
                    <option value="saturday">Saturday</option>
                    <option value="sunday">Sunday</option>
                </select>
            </div>
        `;
    } else if (type === 'condition') {
        return `
            <select name="automation_workflows[${index}][condition_field]" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mb-2">
                <option value="score">Lead Score</option>
                <option value="has_purchased">Has Purchased</option>
                <option value="email_opens">Email Opens</option>
                <option value="tag">Has Tag</option>
            </select>
            <select name="automation_workflows[${index}][condition_operator]" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1 mb-2">
                <option value="greater_than">Greater than</option>
                <option value="equals">Equals</option>
                <option value="less_than">Less than</option>
            </select>
            <input type="text" name="automation_workflows[${index}][condition_value]" placeholder="Value" class="w-full bg-slate-700 text-slate-200 text-xs border border-slate-600 rounded px-2 py-1">
        `;
    }
    return '';
}

function removeNode(btn) {
    const node = btn.closest('.automation-node');
    const connector = node.nextElementSibling;
    if (connector && connector.classList.contains('text-slate-500')) {
        connector.remove();
    }
    node.remove();
}

function toggleConditions(btn) {
    const container = btn.closest('.stage-item').querySelector('.conditions-container');
    container.classList.toggle('hidden');
}

function addCondition(btn, stageIndex) {
    const list = btn.closest('.conditions-container').querySelector('.conditions-list');
    const conditionCount = list.querySelectorAll('.condition-row').length;
    
    const conditionHtml = `
        <div class="condition-row flex items-center gap-2 bg-slate-50 rounded-lg p-2">
            <select name="stages[${stageIndex}][conditions][${conditionCount}][field]" class="border border-slate-300 rounded px-2 py-1 text-sm">
                <option value="email_opened">Lead opened email</option>
                <option value="email_clicked">Lead clicked link</option>
                <option value="score_exceeded">Lead score exceeds</option>
                <option value="has_tag">Lead has tag</option>
                <option value="visited_page">Visited page</option>
                <option value="no_activity">No activity for (days)</option>
            </select>
            <input type="text" name="stages[${stageIndex}][conditions][${conditionCount}][value]" placeholder="Value" class="border border-slate-300 rounded px-2 py-1 text-sm w-32">
            <input type="number" name="stages[${stageIndex}][conditions][${conditionCount}][days]" placeholder="Days" class="border border-slate-300 rounded px-2 py-1 text-sm w-20">
            <button type="button" onclick="removeCondition(this)" class="text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    list.insertAdjacentHTML('beforeend', conditionHtml);
}

function removeCondition(btn) {
    btn.closest('.condition-row').remove();
}
</script>
@endsection