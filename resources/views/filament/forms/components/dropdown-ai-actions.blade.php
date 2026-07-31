<div x-data="{ open: false }" class="dropdown-container relative">
    <button @click="open = !open" wire:loading.attr="disabled"  type="button" class="block w-full px-4 py-2 text-sm">
        <span  class="font-semibold text-sm text-custom-600 dark:text-custom-400 group-hover/link:underline group-focus-visible/link:underline" style="--c-400:var(--raqmix-400);--c-600:var(--raqmix-600);" >
            
            AI Actions
            <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="animate-spin fi-link-icon h-4 w-4 text-custom-600 dark:text-custom-400" style="--c-400:var(--raqmix-400);--c-600:var(--raqmix-600);" wire:loading.delay.default=""> {{-- Corrected Target --}}
                <path clip-rule="evenodd" d="M12 19C15.866 19 19 15.866 19 12C19 8.13401 15.866 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19ZM12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill-rule="evenodd" fill="currentColor" opacity="0.2"></path>
                <path d="M2 12C2 6.47715 6.47715 2 12 2V5C8.13401 5 5 8.13401 5 12H2Z" fill="currentColor"></path>
            </svg>
            <img src="{{asset('images/vendor/ai.png')}}" class="fi-link-icon h-5 w-5 text-custom-600 dark:text-custom-400 inline" wire:loading.remove.delay.default="wire:loading.remove.delay.default">
        </span>
    </button>

    <div x-show="open" class="absolute w-max z-10 w-48 bg-white rounded-md shadow-lg mt-2 right-0" @click.away="open = false">

        @foreach($actions as $action)
            @php($jsfunction = "")
            @foreach($action->getExtraAttributes() as $index => $attr)
                @if("x-on:click" == $index)
                    @php($jsfunction = $attr)
                @endif
            @endforeach
            <button
                class="block w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                type="button"
                x-on:click="{{$jsfunction}}"
                @click="open = false"
                wire:loading.attr="disabled"
                wire:click="mountFormComponentAction('data.{{$action->getName()}}Action', '{{$action->getName()}}' )"
                wire:target="mountFormComponentAction('data.{{$action->getName()}}Action', '{{$action->getName()}}' )"  {{-- Corrected Target --}}
            >
                <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="animate-spin fi-link-icon h-4 w-4 text-custom-600 dark:text-custom-400" style="--c-400:var(--raqmix-400);--c-600:var(--raqmix-600);" wire:loading.delay.default="" wire:target="mountFormComponentAction('data.{{$action->getName()}}Action', '{{$action->getName()}}' )"> {{-- Corrected Target --}}
                    <path clip-rule="evenodd" d="M12 19C15.866 19 19 15.866 19 12C19 8.13401 15.866 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19ZM12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill-rule="evenodd" fill="currentColor" opacity="0.2"></path>
                    <path d="M2 12C2 6.47715 6.47715 2 12 2V5C8.13401 5 5 8.13401 5 12H2Z" fill="currentColor"></path>
                </svg>
                <span class="font-semibold text-sm text-custom-600 dark:text-custom-400 group-hover/link:underline group-focus-visible/link:underline" style="--c-400:var(--raqmix-400);--c-600:var(--raqmix-600);">
                    {{ $action->getLabel() }}
                </span>
            </button>
        @endforeach

    </div>
</div>