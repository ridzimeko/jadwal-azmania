 @props([
    'size' => 8
 ])
 
 <div class="grid place-items-center">
     <div class="inline-block size-{{ $size }} animate-spin rounded-full border-4 border-solid border-current border-e-transparent align-[-0.125em] text-surface motion-reduce:animate-[spin_1.5s_linear_infinite] dark:text-white"
         role="status">
         <span
             class="!absolute !-m-px !h-px !w-px !overflow-hidden !whitespace-nowrap !border-0 !p-0 ![clip:rect(0,0,0,0)]">Loading...</span>
     </div>
 </div>
