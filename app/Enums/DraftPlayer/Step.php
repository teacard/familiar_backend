<?php

namespace App\Enums\DraftPlayer;

/** int 為底：以數值大小比較註冊進度是否「達到/超過」某步驟，供防跳步規則使用 */
enum Step: int
{
    /** 已提交 email，驗證碼已寄出 */
    case EMAIL_SUBMITTED = 1;
    /** email 驗證碼已驗證通過 */
    case CODE_VERIFIED = 2;
    /** 暱稱與大頭照已填寫完成 */
    case PROFILE_COMPLETED = 3;
}
