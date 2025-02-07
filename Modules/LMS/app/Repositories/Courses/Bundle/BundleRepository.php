<?php

namespace Modules\LMS\Repositories\Courses\Bundle;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\LMS\Repositories\BaseRepository;
use Modules\LMS\Models\Courses\Bundle\CourseBundle;

class BundleRepository extends BaseRepository
{
    protected static $model = CourseBundle::class;

    protected static $exactSearchFields = [];

    protected static $excludedFields = [
        'save' => ['courseId', '_token', 'image', 'locale'],
        'update' => ['courseId', '_token', '_method', 'image', 'locale'],
    ];

    protected static $rules = [
        'save' => [
            'title' => 'required',
            'price' => 'required',
            'courseId' => 'required',
        ],
        'update' => [
            'title' => 'required',
            'price' => 'required',
            'courseId' => 'required'
        ],
    ];

    /**
     * save
     *
     * @param Request $request
     */
    public static function save($request): array
    {
        // Check if the request contains an uploaded image file.
        if ($request->hasFile('image')) {
            // Define validation rules for the image upload.
            static::$rules['save'] = [
                'image' => 'required|image|mimes:jpg,png,jpeg,svg,webp',
            ];
            static::$rules['save'];
            // Upload the image and store the thumbnail path.
            $thumbnail = parent::upload($request, fieldname: 'image', file: '', folder: 'lms/courses/bundles');

            // Add the thumbnail path to the request data.
            $request->request->add([
                'thumbnail' => $thumbnail,
            ]);
        }

        // Add a slug version of the title to the request data for URL-friendly usage.
        $request->request->add([
            'slug' => Str::slug($request->title),
        ]);

        // Attempt to save the resource with all request data.
        $response = parent::save($request->all());
        $bundle = $response['data'] ?? null;
        // Check if the save operation was successful.
        if ($response['status'] === 'success' && $bundle) {

            // Attach the selected courses to the newly created resource.
            $bundle->courses()->attach($request->courseId);
            $data = self::translateData($request->all());
            self::translate($bundle, $data, locale: $request->locale ?? app()->getLocale());
        }

        // Return the response from the save operation.
        return $response;
    }

    /**
     * @param  int  $id
     * @param  array  $data
     */
    public static function update($id, $request): array
    {
        $bundleResponse = parent::first($id);
        $bundle = $bundleResponse['data'] ?? null;
        $thumbnail = '';

        if (! $bundle) {
            return [
                'status' => 'error',
                'data' => 'The model not found.',
            ];
        }

        $data = self::translateData($request->all());
        $defaultLanguage = app()->getLocale();
        self::translate(model: $bundle,  data: $data, locale: $request->locale ?? app()->getLocale());

        if ($request->locale &&  $defaultLanguage !== $request->locale) {
            return [
                'status' => 'success',
                'data' => $bundle,
            ];
        }

        if ($request->hasFile('image')) {

            static::$rules['update']['image'] = 'required|image|mimes:jpg,png,jpeg,svg,webp';
            $thumbnail = parent::upload($request, fieldname: 'image', file: $bundle->thumbnail, folder: 'lms/courses/bundles');
        }
        $request->request->add([
            'slug' => Str::slug($request->title),
            'thumbnail' => $thumbnail ? $thumbnail : $bundle->thumbnail,
        ]);
        $bundle = parent::update($id, $request->all());
        if ($bundle['status'] == 'success') {
            $bundle['data']->courses()->sync($request->courseId);
        }

        return $bundle;
    }

    /**
     * delete
     *
     * @param  int  $id
     */
    public static function delete($id, $data = [], $options = [], $relations = []): array
    {
        $response = parent::first($id, withTrashed: true);
        $bundle = $response['data'] ?? null;
        if ($bundle && $response['status'] == 'success') {
            $isDeleteAble = true;
            if (static::isSoftDeleteEnable() && ! $bundle->trashed()) {
                $isDeleteAble = false;
            }
            if ($isDeleteAble) {
                parent::fileDelete(folder: 'lms/courses/bundles', file: $bundle->thumbnail);
            }
            return parent::delete(id: $id);
        }
        return $response;
    }

    /**
     * Retrieve a paginated list of bundles based on user role.
     *
     * @param int $item The number of items per page.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator A paginated list of bundles.
     */
    public function bundleGetByUser($item = 10, $options = [])
    {
        // Initialize the options array based on user role.
        $fields = ['user_id' => authCheck()->id];
        // Prepare the query for the bundle model.

        if (!is_array($options)) {
            $options = array($options);
        }

        $options = array_merge([
            'orderBy' => ['updated_at', 'DESC'],
        ], $options);


        $bundle = static::$model::query();

        foreach ($options as $option => $value) {

            $keys = is_array($value) ? array_keys($value) : [];

            if ($keys && count($keys) === count(array_filter($keys, 'is_int'))) {
                $bundle->{$option}(...$value);
            } else if (empty($value)) {
                $bundle->{$option}();
            } else {
                $bundle->{$option}($value);
            }
        }

        // Retrieve and return a paginated list of bundles based on the determined options.
        return $bundle->with('courses')->where($fields)->paginate($item);
    }

    /**
     * Retrieve a bundle by its ID based on user role.
     *
     * @param int $id The ID of the bundle to retrieve.
     * @return mixed The bundle object if found, null otherwise.
     */
    public function bundleEdit($id, $locale = null)
    {
        // Initialize the options array based on the user's role.
        $options = ['user_id' => authCheck()->id];

        // Prepare the query for the bundle model.
        $bundle = static::$model::query();
        $bundle->with([
            'translations' => function ($query) use ($locale) {
                $query->where('locale', $locale);
            }
        ]);
        // Retrieve and return the first bundle that matches the given ID and options.
        return $bundle->where($options)->firstWhere('id', $id);
    }



    public function thumbnailDelete($id): array
    {

        // If the user is an instructor, set the options for instructor ID.

        $options = ['user_id' => authCheck()->id];
        $model = static::$model::query();

        if ($options) {
            $model->where($options);
        }
        $bundle =  $model->firstWhere('id', $id);
        if (!$bundle) {
            return [
                'status' => 'error'
            ];
        }
        parent::fileDelete(folder: 'lms/courses/bundles', file: $bundle->thumbnail);
        $bundle->thumbnail = null;
        $bundle->update();
        return [
            'status' => 'success'
        ];
    }


    public function countBundleByUser($options = [], $withTrashed = false): array
    {
        try {
            if (!is_array($options)) {
                $options = array($options);
            }

            $model = static::$model::query();

            if ($withTrashed) {
                $model->withTrashed();
            }

            if (!is_array($options)) {
                $options = array($options);
            }

            $options = array_merge([
                'orderBy' => ['updated_at', 'DESC'],
            ], $options);


            $fields = ['user_id' => authCheck()->id];
            // Set options.
            foreach ($options as $option => $value) {
                if (is_array($value)) {
                    $model->{$option}(...$value);
                } else {
                    $model->{$option}($value);
                }
            }

            $bundles = $model->with('courses')->where($fields)->get();
            return [
                'status' => 'success',
                'data' => $bundles,
            ];
        } catch (Exception $ex) {
            return [
                'status' => 'error',
                'data' => $ex->getMessage(),
            ];
        }
    }

    public static function translateData(array $data)
    {
        $data = [
            'title' => $data['title'],
            'details' => $data['details'],
        ];

        return $data;
    }

    public static function translate($model, $data, $locale)
    {
        $model->translations()->updateOrCreate(['locale' => $locale], [
            'locale' => $locale,
            'data' => $data
        ]);
    }
}
