<?php

namespace App\Livewire;

use Illuminate\Validation\Rules\In;
use Livewire\Component;

class PdsChildrenForm extends Component
{
    
    public $children = [
        ['name' => '', 'dob' => '']
    ];

    public function addEmptyChild()
    {   
        Info('Adding an empty child');
        
        $this->children[] = ['name' => '', 'dob' => ''];
    }
/*
    public function mount($children) {
        if (empty($children)) {
            $this->addEmptyChild();
        }
        else {
            $this->children = $children;
        }
    }*/

    public function mount($children)
    {   
        info('Mounting PdsChildrenForm with children: ', $children);
        if (!is_array($children) || empty($children)) {
            $this->addEmptyChild();
        } else {
            $this->children = collect($children)->map(function ($child) {
                return [
                    'name' => $child['name'] ?? '',
                    'dob' => $child['dob'] ?? '',
                ];
            })->toArray();
        }
    }

    public function render()
    {
        return view('livewire.pds-children-form');
    }

    public function removeChild($index)
{   
    info('Removing child at index: ' . $index);
    unset($this->children[$index]);
    $this->children = array_values($this->children);


}
}