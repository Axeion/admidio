<?php
/**
 ***********************************************************************************************
 * @copyright 2004-2017 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

/**
 * @class AdmException
 * @brief Admidio specific enhancements of the exception class
 *
 * This class extends the default PHP exception class with an Admidio specific
 * output. The exception get's a language string as parameter and returns a
 * html or plain text message with the translated error if an exception is thrown
 *
 * @par Example
 * @code try
 * {
 *    if($bla == 1)
 *    {
 *        throw new AdmException(LST_NOT_VALID_DATE_FORMAT);
 *    }
 *    ..
 *
 * }
 * catch(AdmException $e)
 * {
 *    // show html message
 *    $e->showHtml();
 *
 *    // show simply text message
 *    $e->showText();
 * } @endcode
 */
class AdmException extends Exception
{
    protected $params = array();

    /**
     * Constructor that will @b rollback an open database translation
     * @param string   $message Translation @b id that should be shown when exception is catched
     * @param string[] $params  Optional parameter for language string of translation id
     */
    public function __construct($message, $params = array())
    {
        global $gLogger, $gDb;

        if ($gDb instanceof \Database)
        {
            // if there is an open transaction we should perform a rollback
            $gDb->rollback();
        }

        if (is_array($params))
        {
            $this->params = $params;
        }
        else
        {
            // Deprecated
            $paramCount = func_num_args();
            $paramArray = func_get_args();

            for ($paramNumber = 1; $paramNumber < $paramCount; ++$paramNumber)
            {
                $this->params[] = $paramArray[$paramNumber];
            }
        }

        $gLogger->notice('AdmException is thrown!', array('message' => $message, 'params' => $this->params));

        // sicherstellen, dass alles korrekt zugewiesen wird
        parent::__construct($message, 0);
    }

    /**
     * Simply return the plain translated error text without any markup.
     * @return string Returns only a string with the exception text
     */
    public function getText()
    {
        global $gL10n;

        // if text is a translation-id then translate it
        if (strpos($this->message, '_') === 3)
        {
            return $gL10n->get($this->message, $this->params);
        }

        return $this->message;
    }

    /**
     * Set a new Admidio message id with their parameters. This method should be used
     * if during the exception processing a new better message should be set.
     * @param string   $message Translation @b id that should be shown when exception is catched
     * @param string[] $params  Optional parameter for language string of translation id
     */
    public function setNewMessage($message, $params = array())
    {
        $this->message = $message;

        if (is_array($params))
        {
            $this->params = $params;
        }
        else
        {
            // Deprecated
            $this->params = array();

            $paramCount = func_num_args();
            $paramArray = func_get_args();

            for ($paramNumber = 1; $paramNumber < $paramCount; ++$paramNumber)
            {
                $this->params[] = $paramArray[$paramNumber];
            }
        }
    }

    /**
     * Show html message window with translated message
     */
    public function showHtml()
    {
        global $gMessage;

        // display database error to user
        if ($gMessage instanceof \Message)
        {
            $gMessage->show($this->getText());
            // => EXIT
        }
        else
        {
            $this->showText();
            // => EXIT
        }
    }

    /**
     * Simply return the plain translated error text without any markup and stop the script.
     */
    public function showText()
    {
        if (!headers_sent())
        {
            header('Content-type: text/html; charset=utf-8');
        }

        echo $this->getText();
        exit();
    }
}
