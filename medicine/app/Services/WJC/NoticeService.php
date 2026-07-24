<?php

namespace App\Services\WJC;

use App\Exceptions\BusinessException;
use App\Models\WJC\Notice;
use Illuminate\Pagination\LengthAwarePaginator;

class NoticeService
{
    public function list(int $userId, int $page, int $size): LengthAwarePaginator
    {
        return Notice::where('user_id', $userId)
            ->select(['id', 'title', 'content', 'type', 'is_read', 'create_time'])
            ->orderBy('create_time', 'desc')
            ->paginate($size, ['*'], 'page', $page)
            ->through(function ($notice) {
                return [
                    'notice_id'   => $notice->id,
                    'title'       => $notice->title,
                    'content'     => $notice->content,
                    'type'        => $notice->type,
                    'is_read'     => $notice->is_read,
                    'create_time' => $notice->create_time?->format('Y-m-d H:i:s'),
                ];
            });
    }

    public function markRead(int $noticeId, int $userId): void
    {
        $notice = Notice::where('id', $noticeId)
            ->where('user_id', $userId)
            ->first();

        if (!$notice) {
            throw new BusinessException('消息不存在');
        }

        $notice->update(['is_read' => 1]);
    }

    public function markAllRead(int $userId): void
    {
        Notice::where('user_id', $userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
    }

    public function delete(int $noticeId, int $userId): void
    {
        $notice = Notice::where('id', $noticeId)
            ->where('user_id', $userId)
            ->first();

        if (!$notice) {
            throw new BusinessException('消息不存在');
        }

        $notice->delete();
    }
}
