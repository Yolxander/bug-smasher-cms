<?php

namespace App\Http\Controllers;

use App\Models\QaChecklist;
use App\Models\QaChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QaChecklistItemController extends Controller
{
    public function updateItem(Request $request, QaChecklist $qaChecklist, QaChecklistItem $item)
    {
        Log::debug('Updating checklist item', [
            'checklist_id' => $qaChecklist->id,
            'item_id' => $item->id,
            'data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:255',
            'type' => 'required|in:text',
            'is_required' => 'required|boolean',
            'order_number' => 'required|integer',
            'status' => 'required|in:passed,failed,pending',
            'answer' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            Log::warning('Item update failed validation', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->update([
            'item_text' => $request->text,
            'item_type' => $request->type,
            'is_required' => $request->is_required,
            'order_number' => $request->order_number,
            'status' => $request->status,
            'answer' => $request->answer
        ]);

        Log::info('Item updated', ['item_id' => $item->id]);
        return response()->json($item);
    }
}
