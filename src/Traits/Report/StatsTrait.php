<?php

namespace MCMIS\Foundation\Traits\Report;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait StatsTrait
{

    public function stats(Request $request)
    {

        /* set all defaults for reports */
        $status_titles = [];
        $status_ids = array_map(function ($entry) use (&$status_titles) {
            $status_titles[$entry['id']] = $entry['title'];
            return $entry['id'];
        }, $this->getStatuses($request->has('status') ? $request->status : null)->toArray());
        $statuses_default = fill($status_ids, 0);

        $category_titles = [];
        $category_ids = array_map(function ($entry) use (&$category_titles) {
            $category_titles[$entry['id']] = $entry['title'];
            return $entry['id'];
        }, $this->getCategories(($request->has('category') ? $request->category : null))->toArray());
        $categories_default = fill($category_ids, 0);

        /* mapping adding titles */
        $status_wise_category_stats = mapping($status_titles, $categories_default);

        $category_wise_status_stats = mapping($category_titles, $statuses_default);

        $categories = $this->addRequestedFilters($request, $this->filtered_model->create($status_ids, $category_ids)->roleSpecified()->get())
            ->select('complain_categories.id', 'complain_categories.title', 'complaint.status', DB::raw('count(complaint.status) as complaint_count'))
            ->groupBy(['complaint.status', 'complaint.category_id', 'complaint.child_category_id'])->get();

        foreach ($categories as $category) {
            $category_wise_status_stats[$category->id][$category->status] = $category->complaint_count;
            $status_wise_category_stats[$category->status][$category->id] = $category->complaint_count;
        }

        return [
            'raw' => [
                'title' => [
                    'status' => $status_titles,
                    'category' => $category_titles,
                ],
                'id' => [
                    'status' => $status_ids,
                    'category' => $category_ids,
                ],
            ],
            'data' => $categories,
            'output' => [
                'status' => $status_wise_category_stats,
                'category' => $category_wise_status_stats,
            ]
        ];
    }

    public function getStatuses($id = null)
    {
        $statuses = sys('model.status')->orderBy('id');
        if ($id) $statuses = $statuses->where('id', '=', $id);
        return $statuses->get();
    }

    public function getCategories($id = null)
    {
        $no_child_cat = sys('model.category')->whereDoesntHave('children')->where('parent', '=', 0)->get()->lists('id');
        if ($id) {
            $categories = sys('model.category')->where('id', '=', $id)->get();
        } else {
            $categories = sys('model.category')->where('parent', '>', '0')->orWhere(function ($query) use ($no_child_cat) {
                $query->whereIn('id', $no_child_cat->toArray());
            })->get();
        }

        return $categories;
    }
}
