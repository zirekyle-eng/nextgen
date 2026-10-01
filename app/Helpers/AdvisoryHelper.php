<?php

namespace App\Helpers;

use App\User;
use App\Models\Guardian;

/**
 * Advisory Corner Helper - مساعد زاوية المرشد
 * المرشدين هم المعلمين (Teachers)
 */
class AdvisoryHelper
{
    /**
     * جلب جميع المعلمين (المرشدين)
     */
    public static function getTeachers()
    {
        return User::teachers()
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * جلب معلم معين بواسطة ID
     */
    public static function getTeacherById($teacherId)
    {
        return User::teachers()
            ->where('id', $teacherId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * جلب جميع أولياء الأمور
     */
    public static function getParents()
    {
        return User::parents()
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * جلب ولي أمر معين بواسطة ID
     */
    public static function getParentById($parentId)
    {
        return User::parents()
            ->where('id', $parentId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * جلب بيانات Guardian من User
     */
    public static function getParentGuardian($parentId)
    {
        $parent = self::getParentById($parentId);
        if ($parent) {
            return Guardian::where('email', $parent->email)->first();
        }
        return null;
    }

    /**
     * البحث عن معلمين
     */
    public static function searchTeachers($searchTerm)
    {
        return User::teachers()
            ->where('status', 'active')
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('username', 'like', "%{$searchTerm}%");
            })
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * البحث عن أولياء أمور
     */
    public static function searchParents($searchTerm)
    {
        return User::parents()
            ->where('status', 'active')
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('username', 'like', "%{$searchTerm}%");
            })
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * جلب بيانات ولي أمر كاملة (User + Guardian + Students)
     */
    public static function getParentWithDetails($parentId)
    {
        $parent = self::getParentById($parentId);
        if (!$parent) {
            return null;
        }

        $guardian = self::getParentGuardian($parentId);
        
        return [
            'user' => $parent,
            'guardian' => $guardian,
            'students' => $guardian ? $guardian->students : [],
        ];
    }

    /**
     * جلب المعلمين مع عدد محادثاتهم
     */
    public static function getTeachersWithConversationCount()
    {
        return User::teachers()
            ->where('status', 'active')
            ->withCount(['conversations' => function ($query) {
                $query->where('status', '!=', 'closed');
            }])
            ->orderBy('conversations_count', 'desc')
            ->get();
    }
}
