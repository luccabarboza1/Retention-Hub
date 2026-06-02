<table class="w-full text-xs">
    <thead>
        <tr class="text-left text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
            <th class="pb-1 pr-4">Campo</th>
            <th class="pb-1 pr-4">Tipo</th>
            <th class="pb-1 pr-4">Req.</th>
            <th class="pb-1">Observação</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
        @foreach($fields as [$name, $type, $required, $note])
        <tr>
            <td class="py-1.5 pr-4 font-mono text-brand-600 dark:text-brand-400">{{ $name }}</td>
            <td class="pr-4 text-slate-400 dark:text-slate-500">{{ $type }}</td>
            <td class="pr-4">
                @if($required)
                    <span class="text-rose-500 font-bold">sim</span>
                @else
                    <span class="text-slate-300 dark:text-slate-600">—</span>
                @endif
            </td>
            <td class="text-slate-500 dark:text-slate-400">{{ $note }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
