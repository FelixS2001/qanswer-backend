<?php
    
    if(isset($_GET['auth']) && $_GET['auth'] == 'sefeda')
    {
        try
        {
            $database = new PDO(/*Authentication*/);
            $database -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database -> query('SET NAMES utf8');

            $request = (isset($_GET['request'])) ? $_GET['request'] : '';
            $sql = '';
            $response = '';

            switch($request)
            {
                case 'confirmQuestion':
                    $questionID = (isset($_GET['questionid'])) ? $_GET['questionid'] : '';
                    $answerID = (isset($_GET['answerid'])) ? $_GET['answerid'] : '';

                    $sql = 'UPDATE question
                            SET questionState = :questionState
                            WHERE questionID = :questionID;
                            UPDATE answer
                            SET answerState = :answerState
                            WHERE answerID = :answerID';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['questionID' => $questionID,
                                                'questionState' => 'answered',
                                                'answerID' => $answerID,
                                                'answerState' => 'confirmed']);
                    
                    $response = 'Successful: Confirmed question';
                    break;

                    case 'deleteAnswer':
                    $answerID = (isset($_GET['answerid'])) ? $_GET['answerid'] : '';

                    $sql = 'DELETE FROM answer
                            WHERE answerID = :answerID';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['answerID' => $answerID]);
                    
                    $response = 'Successful: Deleted answer';
                    break;
                
                case 'deleteFavoriteQuestion':
                    $userID = (isset($_GET['userid'])) ? $_GET['userid'] : '';
                    $questionID = (isset($_GET['questionid'])) ? $_GET['questionid'] : '';

                    $sql = 'DELETE FROM favoritequestions
                            WHERE userID = :userID AND questionID = :questionID';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['userID' => $userID,
                                                'questionID' => $questionID]);
                    
                    $response = 'Successful: Deleted favorite question';
                    break;

                case 'deleteQuestion':
                    $questionID = (isset($_GET['questionid'])) ? $_GET['questionid'] : '';

                    $sql = 'DELETE FROM question
                            WHERE questionID = :questionID;
                            DELETE FROM answer
                            WHERE questionID = :questionID';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['questionID' => $questionID]);
                    
                    $response ='Successful: Deleted question';
                    break;

                case 'editAnswer':
                    $answerID = (isset($_GET['answerid'])) ? $_GET['answerid'] : '';
                    $answerAnswer = (isset($_GET['answer'])) ? $_GET['answer'] : '';

                    $sql = 'UPDATE answer
                            SET answerAnswer = :answerAnswer
                            WHERE answerID = :answerID';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['answerID' => $answerID,
                                                'answerAnswer' => $answerAnswer]);
                    
                    $response = 'Successful: Updated answer';
                    break;
                
                case 'editQuestion':
                    $questionID = (isset($_GET['questionid'])) ? $_GET['questionid'] : '';
                    $questionTitle = (isset($_GET['questionTitle'])) ? $_GET['questionTitle'] : '';
                    $questionDescription = (isset($_GET['questionDescription'])) ? $_GET['questionDescription'] : '';

                    $sql = 'UPDATE question
                            SET questionTitle = :questionTitle, questionDescription = :questionDescription
                            WHERE questionID = :questionID';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['questionID' => $questionID,
                                                'questionTitle' => $questionTitle,
                                                'questionDescription' => $questionDescription]);
                    
                    $response ='Successful: Updated question';
                    break;

                case 'loadDashboard':
                    $questionCategory = (isset($_GET['category'])) ? $_GET['category'] : '';

                    if($questionCategory == 'Kein Filter')
                    {
                        $sql = 'SELECT q.userID, q.questionID, q.questionTitle, q.questionQuestioner, q.questionUpVotes, q.questionState, DATE_FORMAT(q.questionEntrydate, \'%d.%m.%Y, %H:%i\') AS "questionEntrydate", IFNULL(COUNT(a.questionID), 0) AS `answerCount`
                                FROM question q
                                LEFT OUTER JOIN answer a
                                ON (q.questionID = a.questionID)
                                GROUP BY q.questionTitle, q.questionQuestioner, q.questionUpVotes, q.questionEntrydate
                                ORDER BY q.questionUpVotes DESC, q.questionEntrydate DESC';
                    }
                    else
                    {
                        $sql = 'SELECT q.userID, q.questionID, q.questionTitle, q.questionQuestioner, q.questionUpVotes, q.questionState, DATE_FORMAT(q.questionEntrydate, \'%d.%m.%Y, %H:%i\') AS "questionEntrydate", IFNULL(COUNT(a.questionID), 0) AS `answerCount`
                                FROM question q
                                LEFT OUTER JOIN answer a
                                ON (q.questionID = a.questionID)
                                WHERE q.questionCategory = :questionCategory
                                GROUP BY q.questionTitle, q.questionQuestioner, q.questionUpVotes, q.questionEntrydate
                                ORDER BY q.questionUpVotes DESC, q.questionEntrydate DESC';
                    }

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['questionCategory' => $questionCategory]);
                    
                    $results = $databaseRequest -> fetchAll(PDO::FETCH_ASSOC);
                    $response = json_encode($results);
                    break;

                case 'loadMyFavoriteQuestions':
                    $userID = (isset($_GET['userid'])) ? $_GET['userid'] : '';

                    $sql = 'SELECT f.userID, f.questionID, q.questionQuestioner, q.questionTitle, q.questionDescription, q.questionCategory, q.questionUpVotes, q.questionState, DATE_FORMAT(q.questionEntrydate, \'%d.%m.%Y, %H:%i\') AS "questionEntrydate", IFNULL(COUNT(a.questionID), 0) AS "answerCount"
                            FROM favoritequestions f 
                            JOIN question q 
                            ON (f.questionID = q.questionID)
                            LEFT OUTER JOIN answer a
                            ON (q.questionID = a.questionID)
                            WHERE f.userID = :userID
                            GROUP BY f.userID, f.questionID, q.questionQuestioner, q.questionTitle, q.questionDescription, q.questionCategory, q.questionUpVotes, q.questionState, q.questionEntrydate
                            ORDER BY q.questionUpVotes DESC, q.questionEntrydate DESC';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['userID' => $userID]);
                    
                    $results = $databaseRequest -> fetchAll(PDO::FETCH_ASSOC);
                    $response = json_encode($results);
                    break;
                
                case 'loadMyQuestions':
                    $userID = (isset($_GET['userid'])) ? $_GET['userid'] : '';

                    $sql = 'SELECT u.userID, q.questionID, q.questionQuestioner, q.questionTitle, q.questionDescription, q.questionCategory, q.questionUpVotes, q.questionState, DATE_FORMAT(q.questionEntrydate, \'%d.%m.%Y, %H:%i\') AS "questionEntrydate", IFNULL(COUNT(a.questionID), 0) AS "answerCount"
                            FROM user u
                            LEFT OUTER JOIN question q
                            ON (u.userID = q.userID)
                            LEFT OUTER JOIN answer a
                            ON (q.questionID = a.questionID)
                            WHERE u.userID = :userID
                            GROUP BY u.userID, q.questionID, q.questionQuestioner, q.questionTitle, q.questionDescription, q.questionCategory, q.questionUpVotes, q.questionState, q.questionEntrydate
                            ORDER BY q.questionUpVotes DESC, q.questionEntrydate DESC';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['userID' => $userID]);
                    
                    $results = $databaseRequest -> fetchAll(PDO::FETCH_ASSOC);
                    $response = json_encode($results);
                    break;
                
                case 'loadProfile':
                    $userID = (isset($_GET['userid'])) ? $_GET['userid'] : '';

                    $sql = 'SELECT u.userName, DATE_FORMAT(u.userEntrydate, \'%d.%m.%Y\') AS "userEntrydate", COUNT(DISTINCT q.questionID) AS "questionCount", COUNT(DISTINCT a.answerID) AS "answerCount"
                            FROM user u
                            LEFT OUTER JOIN question q
                            ON (u.userID = q.userID)
                            LEFT OUTER JOIN answer a
                            ON (u.userID = a.userID)
                            WHERE u.userID = :userID
                            GROUP BY u.userName, u.userEntrydate';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['userID' => $userID]);
                    
                    $results = $databaseRequest -> fetchAll(PDO::FETCH_ASSOC);
                    $response = json_encode($results);
                    break;   
                    
                case 'loginUser':
                    $username = (isset($_GET['username'])) ? $_GET['username'] : '';
                    $password = md5((isset($_GET['password'])) ? $_GET['password'] : '');

                    $sql = 'SELECT * 
                            FROM user 
                            WHERE (userName = :username AND userPassword = :password)';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['username' => $username,
                                                'password' => $password]);
                    
                    $results = $databaseRequest -> fetchAll(PDO::FETCH_ASSOC);
                    $response = json_encode($results);
                    break;
                
                case 'newAnswer':
                    $userID = (isset($_GET['userid'])) ? $_GET['userid'] : '';
                    $questionID = (isset($_GET['questionid'])) ? $_GET['questionid'] : '';
                    $answerAnswerer = (isset($_GET['answerer'])) ? $_GET['answerer'] : '';
                    $answerAnswer = (isset($_GET['answer'])) ? $_GET['answer'] : '';
                    $answerEntrydate = date("Y-m-d H:i:s");

                    $sql = 'INSERT INTO answer (userID, questionID, answerAnswerer, answerAnswer, answerState, answerEntrydate)
                            VALUES (:userID, :questionID, :answerAnswerer, :answerAnswer, :answerState, :answerEntrydate)';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['userID' => $userID,
                                                'questionID' => $questionID,
                                                'answerAnswerer' => $answerAnswerer,
                                                'answerAnswer' => $answerAnswer,
                                                'answerState' => 'unconfirmed',
                                                'answerEntrydate' => $answerEntrydate]);
                    
                    $response = 'Successful: Inserted answer';
                    break;

                case 'newFavoriteQuestion':
                    $userID = (isset($_GET['userid'])) ? $_GET['userid'] : '';
                    $questionID = (isset($_GET['questionid'])) ? $_GET['questionid'] : '';

                    $sql = 'INSERT INTO favoritequestions(userID, questionID)
                            VALUES (:userID, :questionID)';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['userID' => $userID,
                                                'questionID' => $questionID]);
                    
                    $response = 'Successful: Inserted favorite question';
                    break;

                case 'newQuestion':
                    $userID = (isset($_GET['userid'])) ? $_GET['userid'] : '';
                    $questionQuestioner = (isset($_GET['questioner'])) ? $_GET['questioner'] : '';
                    $questionTitle = (isset($_GET['questionTitle'])) ? $_GET['questionTitle'] : '';
                    $questionDescription = (isset($_GET['questionDescription'])) ? $_GET['questionDescription'] : '';
                    $questionCategory = (isset($_GET['questionCategory'])) ? $_GET['questionCategory'] : '';
                    $questionEntrydate = date("Y-m-d H:i:s");

                    $sql = 'INSERT INTO question (userID, questionQuestioner, questionTitle, questionDescription, questionCategory, questionState, questionEntrydate)
                            VALUES (:userID, :questionQuestioner, :questionTitle, :questionDescription, :questionCategory, :questionState, :questionEntrydate)';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['userID' => $userID,
                                                'questionQuestioner' => $questionQuestioner,
                                                'questionTitle' => $questionTitle,
                                                'questionDescription' => $questionDescription,
                                                'questionCategory' => $questionCategory,
                                                'questionState' => 'unanswered',
                                                'questionEntrydate' => $questionEntrydate]);
                    
                    $response = 'Successful: Inserted question';
                    break;

                case 'notifyAnswerer':
                    $userID = (isset($_GET['userid'])) ? $_GET['userid'] : '';
                    $questionID = (isset($_GET['questionid'])) ? $_GET['questionid'] : '';
                    $email = '';
                    $questionTitle = '';

                    $sql = 'SELECT userEmail
                            FROM user
                            WHERE userID = :userID';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['userID' => $userID]);

                    while($databaseRespone = $databaseRequest -> fetch(PDO::FETCH_ASSOC))
                    {
                        $email = $databaseRespone['userEmail'];
                    }

                    $sqlQuestionTitle = 'SELECT questionTitle
                                         FROM question
                                         WHERE questionID = :questionID';

                    $databaseRequest = $database -> prepare($sqlQuestionTitle);
                    $databaseRequest -> execute(['questionID' => $questionID]);

                    while($databaseRespone = $databaseRequest -> fetch(PDO::FETCH_ASSOC))
                    {
                        $questionTitle = $databaseRespone['questionTitle'];
                    }
                    
                    mail($email, 'QAnswer: Frage beantwortet', 'Herzlichen Glückwunsch! Sie haben die Frage: "' . $questionTitle . '" auf QAnswer richtig beantwortet', 'From: QAnswer');
                    $response = 'Successful: Notified answerer';
                    break;

                case 'notifyQuestioner':
                    $userID = (isset($_GET['userid'])) ? $_GET['userid'] : '';
                    $questionID = (isset($_GET['questionid'])) ? $_GET['questionid'] : '';
                    $email = '';
                    $questionTitle = '';

                    $sql = 'SELECT userEmail
                            FROM user
                            WHERE userID = :userID';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['userID' => $userID]);
                    
                    while($databaseRespone = $databaseRequest -> fetch(PDO::FETCH_ASSOC))
                    {
                        $email = $databaseRespone['userEmail'];
                    }

                    $sqlQuestionTitle = 'SELECT questionTitle
                                         FROM question
                                         WHERE questionID = :questionID';

                    $databaseRequest = $database -> prepare($sqlQuestionTitle);
                    $databaseRequest -> execute(['questionID' => $questionID]);

                    while($databaseRespone = $databaseRequest -> fetch(PDO::FETCH_ASSOC))
                    {
                        $questionTitle = $databaseRespone['questionTitle'];
                    }
                    
                    mail($email, 'QAnswer: Antwort erstellt', 'Ein anderer Benutzer hat auf Ihre Frage: "' . $questionTitle . '" auf QAnswer geantwortet', 'From: QAnswer');
                    $response = 'Successful: Notified questioner';
                    break;
                
                case 'registerUser':
                    $username = (isset($_GET['username'])) ? $_GET['username'] : '';
                    $password = md5((isset($_GET['password'])) ? $_GET['password'] : '');
                    $email = (isset($_GET['email'])) ? $_GET['email'] : '';
                    $entrydate = date("Y-m-d H:i:s");

                    $sql = 'INSERT INTO user (userName, userPassword, userEmail, userEntrydate) 
                            VALUES (:username, :password, :email, :entrydate)';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['username' => $username,
                                                'password' => $password,
                                                'email' => $email,
                                                'entrydate' => $entrydate]);
                    
                    $response = 'Successful: Inserted user';
                    break;

                case 'updateDownvote':
                    $questionID = (isset($_GET['questionid'])) ? $_GET['questionid'] : '';
                    $newValue = '';

                    $sql = 'UPDATE question
                            SET questionUpVotes = questionUpVotes - 1
                            WHERE questionID = :questionID';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['questionID' => $questionID]);

                    $response = 'Successful: Updated downvote';
                    break;
                
                case 'updateUpvote':
                    $questionID = (isset($_GET['questionid'])) ? $_GET['questionid'] : '';

                    $sql = 'UPDATE question
                            SET questionUpVotes = questionUpVotes + 1
                            WHERE questionID = :questionID';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['questionID' => $questionID]);

                    $response = 'Successful: Updated upvote';
                    break;

                case 'viewQuestion':
                    $questionID = (isset($_GET['questionid'])) ? $_GET['questionid'] : '';

                    $sql = 'SELECT q.userID, q.questionID, q.questionQuestioner, q.questionTitle, q.questionDescription, q.questionCategory, q.questionUpVotes, q.questionState, DATE_FORMAT(q.questionEntrydate, \'%d.%m.%Y, %H:%i\') AS "questionEntrydate", a.userID AS "answerUserID", a.answerID, a.answerAnswerer, a.answerAnswer, a.answerState, DATE_FORMAT(a.answerEntrydate, \'%d.%m.%Y, %H:%i\') AS "answerEntrydate"
                            FROM question q
                            LEFT OUTER JOIN answer a
                            ON (q.questionID = a.questionID)
                            WHERE q.questionID = :questionID
                            ORDER BY a.answerEntrydate DESC';

                    $databaseRequest = $database -> prepare($sql);
                    $databaseRequest -> execute(['questionID' => $questionID]);
                    
                    $results = $databaseRequest -> fetchAll(PDO::FETCH_ASSOC);
                    $response = json_encode($results);
                    break;  
                    
                default:
                    echo 'Error: Request not found';
                    break;
            }

            if($response != '[]')
            {
                echo $response;
            }
            else if(is_null($response))
            {
                echo 'Error: Null-Pointer';
            }
            else
            {
                echo 'Error: Returned 0 lines';
            }
        }
        catch(PDOException $e)
        {
            echo 'Error: Database error: ' . $e->getMessage();
        }
    }
    else
    {
        echo 'Error: Permission denied';
    }

?>
