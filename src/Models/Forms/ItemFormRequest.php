<?php

namespace WalkerChiu\MallWishlist\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class ItemFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if (
            $request->isMethod('put')
            && empty($data['id'])
            && isset($request->id)
        ) {
            $data['id'] = (int) $request->id;
            $this->getInputSource()->replace($data);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return Array
     */
    public function attributes()
    {
        return [
            'user_id'  => trans('php-mall-wishlist::system.user_id'),
            'stock_id' => trans('php-mall-wishlist::system.stock_id')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'user_id'  => ['required','integer','min:1','exists:'.config('wk-core.table.user').',id'],
            'stock_id' => ['required','integer','min:1','exists:'.config('wk-core.table.mall-shelf.stocks').',id']
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','integer','min:1','exists:'.config('wk-core.table.mall-wishlist.items').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'id.required'       => trans('php-core::validation.required'),
            'id.integer'        => trans('php-core::validation.integer'),
            'id.min'            => trans('php-core::validation.min'),
            'id.exists'         => trans('php-core::validation.exists'),
            'user_id.required'  => trans('php-core::validation.required'),
            'user_id.integer'   => trans('php-core::validation.integer'),
            'user_id.min'       => trans('php-core::validation.min'),
            'user_id.exists'    => trans('php-core::validation.exists'),
            'stock_id.required' => trans('php-core::validation.required'),
            'stock_id.integer'  => trans('php-core::validation.integer'),
            'stock_id.min'      => trans('php-core::validation.min'),
            'stock_id.exists'   => trans('php-core::validation.exists')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        if ( !empty(config('wk-core.class.mall-shelf.stock')) ) {
            $validator->after( function ($validator) {
                $data = $validator->getData();
                if (
                    config('wk-mall-wishlist.onoff.mall-shelf')
                    && (
                        isset($data['stock_id'])
                        || isset($data['nums'])
                    )
                ) {
                    if (isset($data['stock_id'])) {
                        $result = DB::table(config('wk-core.table.mall-shelf.stocks'))
                                    ->where('is_enabled', 1)
                                    ->where('id', $data['stock_id'])
                                    ->exists();
                        if (!$result)
                            $validator->errors()->add('stock_id', trans('php-core::validation.exists'));

                        if (isset($data['nums'])) {
                            $result = DB::table(config('wk-core.table.mall-shelf.stocks'))
                                        ->where('is_enabled', 1)
                                        ->where('id', $data['stock_id'])
                                        ->where('quantity', '>=', $data['nums'])
                                        ->exists();
                            if (!$result)
                                $validator->errors()->add('nums', trans('php-core::validation.max'));
                        }
                    }
                }
            });
        }
    }
}
