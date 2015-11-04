<?php

namespace History\Entities\Models;

use History\Entities\Traits\HasEvents;
use Illuminate\Support\Arr;

/**
 * @property int      choice
 * @property string   answer
 * @property Question question
 */
class Vote extends AbstractModel
{
    use HasEvents;

    /**
     * @var array
     */
    protected $fillable = [
        'choice',
        'question_id',
        'user_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'choice' => 'integer',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->question->request();
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// ATTRIBUTES /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the textual representation of a choice.
     *
     * @return string
     */
    public function getAnswerAttribute()
    {
        $choices = $this->question ? $this->question->choices : [];
        $choice  = Arr::get($choices, $this->choice - 1, $this->choice);

        return ucfirst($choice);
    }

    /**
     * @return bool
     */
    public function isPositive()
    {
        return !preg_match("/(\s|^)(no|don't|keep|none|do not)(\s|$)/i", $this->answer);
    }
}
