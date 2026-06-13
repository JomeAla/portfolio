<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\SlugGenerator;

class Segment extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'name';
    
    protected $fillable = [
        'name',
        'description',
        'conditions',
        'is_active',
        'auto_tag_key',
        'sync_frequency',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public static function conditionTypes(): array
    {
        return [
            'score_above' => 'Score Above',
            'score_below' => 'Score Below',
            'score_between' => 'Score Between',
            'has_tag' => 'Has Tag',
            'missing_tag' => 'Missing Tag',
            'status' => 'Lead Status',
            'source' => 'Lead Source',
            'utm_source' => 'UTM Source',
            'utm_medium' => 'UTM Medium',
            'utm_campaign' => 'UTM Campaign',
            'has_email' => 'Has Email Domain',
            'is_newsletter' => 'Newsletter Subscriber',
            'is_confirmed' => 'Confirmed Subscriber',
            'created_after' => 'Created After',
            'created_before' => 'Created Before',
            'last_activity_after' => 'Last Activity After',
            'opened_emails_above' => 'Opened Emails Above',
            'clicked_emails_above' => 'Clicked Emails Above',
            'purchased' => 'Has Purchased',
            'no_activity_30d' => 'No Activity 30+ Days',
            'lead_score_hot' => 'Hot Lead (100+)',
            'lead_score_warm' => 'Warm Lead (50-99)',
            'lead_score_cold' => 'Cold Lead (< 50)',
        ];
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'segment_leads')
            ->withTimestamps()
            ->withPivot('synced_at');
    }

    public function applyConditions($query)
    {
        $conditions = $this->conditions ?? [];
        
        if (empty($conditions)) {
            return $query;
        }

        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? '';
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? '';
            $value2 = $condition['value2'] ?? '';

            switch ($field) {
                case 'score_above':
                    $query->where('score', '>', (int) $value);
                    break;
                case 'score_below':
                    $query->where('score', '<', (int) $value);
                    break;
                case 'score_between':
                    $query->whereBetween('score', [(int) $value, (int) $value2]);
                    break;
                case 'status':
                    $query->where('status', $value);
                    break;
                case 'source':
                    $query->where('source', $value);
                    break;
                case 'utm_source':
                    $query->where('utm_source', $value);
                    break;
                case 'utm_medium':
                    $query->where('utm_medium', $value);
                    break;
                case 'utm_campaign':
                    $query->where('utm_campaign', $value);
                    break;
                case 'is_newsletter':
                    $query->where('is_newsletter', true);
                    break;
                case 'is_confirmed':
                    $query->where('confirmed', true);
                    break;
                case 'created_after':
                    $query->where('created_at', '>', $value);
                    break;
                case 'created_before':
                    $query->where('created_at', '<', $value);
                    break;
                case 'has_tag':
                    if ($value) {
                        $tagId = is_numeric($value) ? (int) $value : null;
                        if ($tagId) {
                            $query->whereHas('tags', fn($q) => $q->where('tags.id', $tagId));
                        } else {
                            $query->whereHas('tags', fn($q) => $q->where('tags.name', $value));
                        }
                    }
                    break;
                case 'missing_tag':
                    if ($value) {
                        $tagId = is_numeric($value) ? (int) $value : null;
                        if ($tagId) {
                            $query->whereDoesntHave('tags', fn($q) => $q->where('tags.id', $tagId));
                        } else {
                            $query->whereDoesntHave('tags', fn($q) => $q->where('tags.name', $value));
                        }
                    }
                    break;
                case 'purchased':
                    $query->whereExists(fn($q) => $q->select(1)->from('orders')->whereColumn('orders.customer_email', 'leads.email')->where('orders.payment_status', 'success'));
                    break;
                case 'no_activity_30d':
                    $query->whereDoesntHave('activities', fn($q) => $q->where('created_at', '>', now()->subDays(30)));
                    break;
                case 'lead_score_hot':
                    $query->where('score', '>=', 100);
                    break;
                case 'lead_score_warm':
                    $query->whereBetween('score', [50, 99]);
                    break;
                case 'lead_score_cold':
                    $query->where('score', '<', 50);
                    break;
                case 'has_email':
                    if ($value) {
                        $query->where('email', 'LIKE', '%@' . $value);
                    }
                    break;
                case 'opened_emails_above':
                    $sub = \DB::table('email_queue')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('lead_id', 'leads.id')
                        ->where('opened', true);
                    $query->where(function ($q) use ($value, $sub) {
                        $q->whereRaw("({$sub->toSql()}) > ?", [(int) $value]);
                    })->mergeBindings($sub);
                    break;
                case 'clicked_emails_above':
                    $sub = \DB::table('email_queue')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('lead_id', 'leads.id')
                        ->where('clicked', true);
                    $query->where(function ($q) use ($value, $sub) {
                        $q->whereRaw("({$sub->toSql()}) > ?", [(int) $value]);
                    })->mergeBindings($sub);
                    break;
            }
        }

        return $query;
    }

    public function getLeadsCount(): int
    {
        return $this->leads()->count();
    }

    public function syncLeads(): array
    {
        $matchingLeads = $this->applyConditions(Lead::query())->pluck('id')->toArray();
        $currentMemberIds = $this->leads()->pluck('leads.id')->toArray();

        $toAdd = array_diff($matchingLeads, $currentMemberIds);
        $toRemove = array_diff($currentMemberIds, $matchingLeads);

        $tagged = 0;
        $untagged = 0;

        if (!empty($toAdd)) {
            $now = now();
            $pivotRows = array_map(fn($id) => [
                'segment_id' => $this->id,
                'lead_id' => $id,
                'created_at' => $now,
                'updated_at' => $now,
                'synced_at' => $now,
            ], $toAdd);
            \DB::table('segment_leads')->insert($pivotRows);

            if ($this->auto_tag_key) {
                $tag = Tag::firstOrCreate(['name' => $this->auto_tag_key]);
                Lead::whereIn('id', $toAdd)->each(fn($lead) => $lead->tags()->syncWithoutDetaching([$tag->id]));
            }
            $tagged = count($toAdd);
        }

        if (!empty($toRemove)) {
            $this->leads()->detach($toRemove);

            if ($this->auto_tag_key) {
                $tag = Tag::where('name', $this->auto_tag_key)->first();
                if ($tag) {
                    Lead::whereIn('id', $toRemove)->each(fn($lead) => $lead->tags()->detach($tag->id));
                }
            }
            $untagged = count($toRemove);
        }

        return ['added' => $tagged, 'removed' => $untagged];
    }
}