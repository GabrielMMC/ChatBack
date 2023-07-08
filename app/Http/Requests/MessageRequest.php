<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'content.required' => 'O conteúdo é obrigatório',
            'type.required' => 'O tipo é obrigatório',
            'receiver.required' => 'O remetente é obrigatório',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch (strtolower($this->route()->getActionMethod())):
            case 'store_message':
                return [
                    'content' => 'required',
                    'type' => 'required',
                    'receiver' => 'required'
                ];

            case 'store_image_message':
                return [
                    'type' => 'required',
                    'receiver' => 'required'
                ];

            case 'store_audio_message':
                return [
                    'type' => 'required',
                    'receiver' => 'required'
                ];

            default:
                return [];
        endswitch;
    }
}
