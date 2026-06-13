<?php
// Simple test route for saving funnel - no controller complexity
// Add to routes/web.php:

// Route::post('/test-save-funnel/{id}', function($id) {
//     $data = request()->all();
//     $fillable = ['name', 'slug', 'description', 'goal', 'funnel_type', 'product_id', 'service_id', 
//                'welcome_sequence_id', 'followup_sequence_id', 'notify_email', 
//                'upsell_product_id', 'upsell_discount', 'upsell_timer', 'countdown_hours'];
//     
//     $update = [];
//     foreach($fillable as $field) {
//         if(request()->has($field)) {
//             $update[$field] = request($field);
//         }
//     }
//     
//     if(empty($update)) {
//         return ['error' => 'No data'];
//     }
//     
//     DB::table('funnels')->where('id', $id)->update($update);
//     return ['success' => true, 'updated' => $update];
// });

echo "Test route created. Add this to routes/web.php around line 530:";

$code = "
// Test Save Funnel
Route::post('/test-save-funnel/{id}', function(\$id) {
    \$fields = ['name', 'description', 'goal', 'funnel_type', 'product_id', 'service_id', 
               'welcome_sequence_id', 'followup_sequence_id', 'notify_email', 
               'upsell_product_id', 'upsell_discount', 'upsell_timer', 'countdown_hours'];
    
    \$update = [];
    foreach(\$fields as \$field) {
        \$val = request(\$field);
        if(\$val !== null) {
            \$update[\$field] = \$val;
        }
    }
    
    if(empty(\$update)) {
        return response()->json(['error' => 'No data provided']);
    }
    
    \$update['updated_at'] = now();
    
    DB::table('funnels')->where('id', \$id)->update(\$update);
    
    return response()->json(['success' => true, 'message' => 'Saved!', 'data' => \$update]);
});
";

echo $code;