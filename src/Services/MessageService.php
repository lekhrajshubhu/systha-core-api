<?php

namespace Systha\Core\Services;

use Exception;
use InvalidArgumentException;
use Systha\Core\Models\ChatConversation;

class MessageService extends BaseService
{
    public function sendMessage($model, $modelFrom, $modelTo, $messageContent)
    {
        try {
            if (!isset($model->id) || !isset($model->vendor_id) || !isset($model->client_id)) {
                throw new InvalidArgumentException('Invalid model provided');
            }
            $tableName = $model->getTable();
            $tableId = $model->id;
            $conversation = ChatConversation::where('table_name', $tableName)
                ->where('table_id', $tableId)
                ->first();
            if (!$conversation) {
                $conversationData = [
                    'table_name' => $tableName,
                    'table_id' => $tableId,
                    'title' => $this->getTitle($model),
                    'vendor_id' => $model->vendor_id ?? null,
                    'client_id' => $model->client_id ?? null,
                    'is_active' => 1,
                ];

                if (isset($model->service_provider_id)) {
                    $conversationData['provider_id'] = $model->service_provider_id;
                }
                if (isset($model->provider_id)) {
                    $conversationData['provider_id'] = $model->provider_id;
                }
                $conversation = ChatConversation::create($conversationData);
            }

            if (!$conversation->members()->where('table_name', $modelFrom->getTable())->where('table_id', $modelFrom->id)->exists()) {
                $conversation->members()->create([
                    'table_name' => $modelFrom->getTable(),
                    'table_id' => $modelFrom->id,
                ]);
            }

            if (!$conversation->members()->where('table_name', $modelTo->getTable())->where('table_id', $modelTo->id)->exists()) {
                $conversation->members()->create([
                    'table_name' => $modelTo->getTable(),
                    'table_id' => $modelTo->id,
                ]);
            }

            $message = $conversation->messages()->create([
                'table_from' => $modelFrom->getTable(),
                'table_from_id' => $modelFrom->id,
                'table_to' => $modelTo->getTable(),
                'table_to_id' => $modelTo->id,
                'message' => $messageContent,
            ]);

            if ($modelFrom->getTable() == 'vendors') {
                $message->seen_vendor = 1;
                $message->save();
            }
            if ($modelFrom->getTable() == 'clients') {
                $message->seen_client = 1;
                $message->save();
            }
            if ($modelFrom->getTable() == 'service_providers') {
                $message->seen_provider = 1;
                $message->save();
            }

            return $conversation;
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred. Please try again later.'], 500);
        }
    }

    public function getTitle($model)
    {
        switch ($model->getTable()) {
            case 'appointments':
                return $model->appointment_no;
            case 'package_subscriptions':
                return $model->subs_no;
            case 'quote_enqs':
                return $model->enq_no;
            case 'quotes':
                return $model->quote_number;
            default:
                return null;
        }
    }
}
