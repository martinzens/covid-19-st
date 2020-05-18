<?php 

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use App\V1\Models\StudyComponent;
use App\V1\Models\ParticipantStudy;
use App\V1\Models\ParticipantStudyActivity;
use App\V1\Models\StudyComponentTransition;

class GermanyMetrics extends Command {

    const BASIC_ID = 347;
    const SYMPTOMS_ID = 349;
    const SYMPTOMS_POSITIVE_ID = 351;

    // Basic module

    const QUESTION_GENDER = 831;
    const ANSWER_GENDER_FEMALE = 4291;

    const QUESTION_COUNTRY = 843;
    const ANSWER_COUNTRY_GERMANY = 4466;

    const QUESTION_AGE_GROUP = 701;

    const ANSWER_AGE_GROUP_30 = 4283;
    const ANSWER_AGE_GROUP_31_40 = 4284;
    const ANSWER_AGE_GROUP_41_50 = 4285;
    const ANSWER_AGE_GROUP_51_60 = 4286;
    const ANSWER_AGE_GROUP_61_70 = 4287;
    const ANSWER_AGE_GROUP_71_80 = 4288;
    const ANSWER_AGE_GROUP_81 = 4289;

    const QUESTION_SMOKER = 837;
    const QUESTION_DIABETES = 713;
    const QUESTION_HYPERTENSION = 714;
    const QUESTION_INFLUENZA_SHOT = 711;
    const QUESTION_CHRONIC_LUNG_DISEASE = 703;
    const QUESTION_CHRONIC_HEARTH_DISEASE = 705;


    // Symptoms & Symptoms positive

    const QUESTION_OTHER_SYMPTOMS = 816;
    const QUESTION_SYMPTOMS_POSITIVE_OTHER_SYMPTOMS = 827;

    const QUESTION_SNIF = 727;
    const QUESTION_SYMPTOMS_POSITIVE_SNIF = 773;

    const QUESTION_FEVER = 722;
    const SYMPTOMS_POSITIVE_QUESTION_FEVER = 768;

    const QUESTION_CHILLS = 723;
    const QUESTION_SYMPTOMS_POSITIVE_CHILLS = 769;

    const QUESTION_COUGH = 726;
    const QUESTION_SYMPTOMS_POSITIVE_COUGH = 771;

    const QUESTION_VOMIT = 729;
    const QUESTION_SYMPTOMS_POSITIVE_VOMIT = 775;

    const QUESTION_FATIGUE = 724;
    const QUESTION_SYMPTOMS_POSITIVE_FATIGUE = 770;

    const QUESTION_DIARREA = 728;
    const QUESTION_SYMPTOMS_POSITIVE_DIARREA = 774;

    const QUESTION_HEAD_ACHE = 732;
    const QUESTION_SYMPTOMS_POSITIVE_HEAD_ACHE = 777;

    const QUESTION_BODY_ACHES = 725;
    const QUESTION_SYMPTOMS_POSITIVE_BODY_ACHES = 772;

    const QUESTION_SORE_THROAT = 731;
    const QUESTION_SYMPTOMS_POSITIVE_SORE_THROAT = 776;

    const QUESTION_SHORTNESS_OF_BREATH = 733;
    const QUESTION_SYMPTOMS_POSITIVE_SHORTNESS_OF_BREATH = 778;

    const QUESTION_ALTERED_SENSE_OF_SMELL = 807;
    const QUESTION_SYMPTOMS_POSITIVE_ALTERED_SENSE_OF_SMELL = 825;


    const QUESTION_HAVE_YOU_BEEN_TESTED = 810;

    const ANSWER_HAVE_YOU_BEEN_TESTED_POSITIVE = 4369;
    const ANSWER_HAVE_YOU_BEEN_TESTED_NEGATIVE = 4372;
    const ANSWER_HAVE_YOU_BEEN_TESTED_WAITING_RESULTS = 4371;
    const ANSWER_HAVE_YOU_BEEN_TESTED_NOT_TESTED = 4370;


    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'metrics:germany';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate metrics for Germany population.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->printGermanParticipantsStatistics();
    }

    private function ageGroupAverage($ageGroupAnswerId)
    {
        switch ($ageGroupAnswerId) {
            case self::ANSWER_AGE_GROUP_30:
                return 24;
                break;
            case self::ANSWER_AGE_GROUP_31_40:
                return 36;
                break;
            case self::ANSWER_AGE_GROUP_41_50:
                return 46;
                break;
            case self::ANSWER_AGE_GROUP_51_60:
                return 56;
                break;
            case self::ANSWER_AGE_GROUP_61_70:
                return 66;
                break;
            case self::ANSWER_AGE_GROUP_71_80:
                return 76;
                break;
            case self::ANSWER_AGE_GROUP_81:
                return 86;
                break;
        }
    }

    private function symptomsQuestionsIds()
    {
        return [
            'symptoms_module' => [
                'fever' => self::QUESTION_FEVER,
                'cough' => self::QUESTION_COUGH,
                'chills' => self::QUESTION_CHILLS,
                'diarrhea' => self::QUESTION_DIARREA,
                'fatigue' => self::QUESTION_FATIGUE,
                'head ache' => self::QUESTION_HEAD_ACHE,
                'snif and snort' => self::QUESTION_SNIF,
                'nausea or vomit' => self::QUESTION_VOMIT,
                'body aches' => self::QUESTION_BODY_ACHES,
                'sore throat' => self::QUESTION_SORE_THROAT,
                'shortness of breath' => self::QUESTION_SHORTNESS_OF_BREATH,
                'altered sense of smell' => self::QUESTION_ALTERED_SENSE_OF_SMELL,
            ],
            'symptoms_positive_module' => [
                'fever' => self::SYMPTOMS_POSITIVE_QUESTION_FEVER,
                'cough' => self::QUESTION_SYMPTOMS_POSITIVE_COUGH,
                'chills' => self::QUESTION_SYMPTOMS_POSITIVE_CHILLS,
                'diarrhea' => self::QUESTION_SYMPTOMS_POSITIVE_DIARREA,
                'fatigue' => self::QUESTION_SYMPTOMS_POSITIVE_FATIGUE,
                'snif and snort' => self::QUESTION_SYMPTOMS_POSITIVE_SNIF,
                'head ache' => self::QUESTION_SYMPTOMS_POSITIVE_HEAD_ACHE,
                'nausea or vomit' => self::QUESTION_SYMPTOMS_POSITIVE_VOMIT,
                'body aches' => self::QUESTION_SYMPTOMS_POSITIVE_BODY_ACHES,
                'sore throat' => self::QUESTION_SYMPTOMS_POSITIVE_SORE_THROAT,
                'shortness of breath' => self::QUESTION_SYMPTOMS_POSITIVE_SHORTNESS_OF_BREATH,
                'altered sense of smell' => self::QUESTION_SYMPTOMS_POSITIVE_ALTERED_SENSE_OF_SMELL,
            ]
        ];
    }

    private function preConditions()
    {
        return [
            'smokers' => self::QUESTION_SMOKER,
            'diabetes' => self::QUESTION_DIABETES,
            'hypertension' => self::QUESTION_HYPERTENSION,
            'influenza shot' => self::QUESTION_INFLUENZA_SHOT,
            'chronic lung disease' => self::QUESTION_CHRONIC_LUNG_DISEASE,
            'chronic hearth disease' => self::QUESTION_CHRONIC_HEARTH_DISEASE,
        ];
    }


    private function printGermanParticipantsStatistics()
    {
        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);
        $this->info('---- Positive Participants ----');
        $this->printParticipantStatistics($germanPositiveParticipantsQuery);

        $germanNegativeParticipantsQuery = $this->germanNegativeParticipants(true);
        $this->info('---- Negative Participants ----');
        $this->printParticipantStatistics($germanNegativeParticipantsQuery);

        $germanNotTestedParticipantsQuery = $this->germanNotTestedParticipants(true);
        $this->info('---- Not tested Participants ----');
        $this->printParticipantStatistics($germanNotTestedParticipantsQuery);
    }


    private function printParticipantStatistics($participantsQuery)
    {
        $preConditionsQuestionIds = $this->preConditions();
        $symptomsQuestionsIds = $this->symptomsQuestionsIds();
        $totalOfParticipants = (clone $participantsQuery)->get(['id'])->count();
        $femaleParticipantsCount = $this->femaleCount((clone $participantsQuery));

        $this->info('    Total: ' . $totalOfParticipants);
        $this->info('    Female: ' . $femaleParticipantsCount . ' (' . floor(($femaleParticipantsCount * 100)/$totalOfParticipants) . '%)');

        $averageAge = $this->participantAverageAge((clone $participantsQuery), $totalOfParticipants);

        $this->info('        Average age: ' . $averageAge);

        $this->info('    Pre existing conditions:');

        foreach ($preConditionsQuestionIds as $key => $questionId) {
            $participantsToProcess = clone $participantsQuery;
            $count = $this->anweredYesToCondition($participantsToProcess, $questionId);

            $this->info('    ' . $key . ': ' . $count . '(' . floor(($count*100)/$totalOfParticipants) . '%)');
        }

        $this->info('    Symptoms:');

        foreach ($symptomsQuestionsIds['symptoms_module'] as $key => $value) {

            $participantsToProcess = clone $participantsQuery;

            $paticipantsThatExperimentedSymptom = $this->anweredYesToSymptom($participantsToProcess,
                $symptomsQuestionsIds['symptoms_module'][$key],
                $symptomsQuestionsIds['symptoms_positive_module'][$key]
            );

            $numberOfParticipantsThatExperimentedSymptom = $paticipantsThatExperimentedSymptom->count();
            $percentage = floor(($numberOfParticipantsThatExperimentedSymptom * 100) / $totalOfParticipants);

            $this->info('    - ' . $key . ': ' . $numberOfParticipantsThatExperimentedSymptom . ' (' . $percentage .'%)');
        }


    }


    // Participants that have activities, that have steps in which they answered that they were german
    private function getGermanParticipants($returnQuery = false)
    {
        $basicModuleId = self::BASIC_ID;
        $countryModuleComponentId = self::QUESTION_COUNTRY;
        $germanyAnswerId = self::ANSWER_COUNTRY_GERMANY;

        $germanParticipantsQuery = ParticipantStudy::whereHas('activities', function($query)
            use ($basicModuleId, $countryModuleComponentId, $germanyAnswerId) {
                $query->forStudyComponent($basicModuleId)
                    ->completed()
                    ->whereHas('steps', function($query) use ($countryModuleComponentId, $germanyAnswerId) {
                        $query->where('answer_text', 'a:1:{i:0;i:' . $germanyAnswerId  . ';}');
                    });
                });

        if ($returnQuery) {
            return $germanParticipantsQuery;
        }

        return  $germanParticipantsQuery->get(['id', 'participant_id']);

    }


    private function germanPositiveParticipants($returnQuery = false)
    {
        $symptomsModuleId = self::SYMPTOMS_ID;
        $positiveAnswerId = self::ANSWER_HAVE_YOU_BEEN_TESTED_POSITIVE;

        $germanPositiveParticipantsQuery = $this->getGermanParticipants(true)
            ->whereHas('activities', function($query)
                use ($symptomsModuleId, $positiveAnswerId) {
                    $query->forStudyComponent($symptomsModuleId)
                        ->completed()
                        ->whereHas('steps', function($query) use ($positiveAnswerId) {
                            $query->where('answer_text', 'a:1:{i:0;i:' . $positiveAnswerId  . ';}');
                    });
            });

        if ($returnQuery) {
            return $germanPositiveParticipantsQuery;
        }

        return $germanPositiveParticipantsQuery->get(['id', 'participant_id']);
    }


    private function germanNegativeParticipants($returnQuery = false)
    {
        $symptomsModuleId = self::SYMPTOMS_ID;
        $negativeAnswerId = self::ANSWER_HAVE_YOU_BEEN_TESTED_NEGATIVE;

        $germanPositiveParticipantsQuery = $this->getGermanParticipants(true)
            ->whereHas('activities', function($query)
                use ($symptomsModuleId, $negativeAnswerId) {
                    $query->forStudyComponent($symptomsModuleId)
                        ->completed()
                        ->whereHas('steps', function($query) use ($negativeAnswerId) {
                            $query->where('answer_text', 'a:1:{i:0;i:' . $negativeAnswerId  . ';}');
                    });
            });

        if ($returnQuery) {
            return $germanPositiveParticipantsQuery;
        }

        return $germanPositiveParticipantsQuery->get(['id', 'participant_id']);
    }


    private function germanNotTestedParticipants($returnQuery = false)
    {
        $symptomsModuleId = self::SYMPTOMS_ID;
        $positiveAnswerId = self::ANSWER_HAVE_YOU_BEEN_TESTED_POSITIVE;
        $negativeAnswerId = self::ANSWER_HAVE_YOU_BEEN_TESTED_NEGATIVE;

        $germanNotTestedParticipantsQuery = $this->getGermanParticipants(true)
            ->whereDoesntHave('activities', function($query)
                use ($symptomsModuleId, $positiveAnswerId, $negativeAnswerId) {
                    $query->forStudyComponent($symptomsModuleId)
                        ->completed()
                        ->whereHas('steps', function($query) use ($positiveAnswerId) {
                            $query->where('answer_text', 'a:1:{i:0;i:' . $positiveAnswerId  . ';}');
                    })
                    ->orWhereHas('steps', function($query) use ($negativeAnswerId) {
                            $query->where('answer_text', 'a:1:{i:0;i:' . $negativeAnswerId  . ';}');
                    });
            });

        if ($returnQuery) {
            return $germanNotTestedParticipantsQuery;
        }

        return $germanNotTestedParticipantsQuery->get(['id', 'participant_id']);
    }


    private function femaleCount($participantsQuery)
    {
        $basicModuleId = self::BASIC_ID;
        $femaleAnswerId = self::ANSWER_GENDER_FEMALE;

        return $participantsQuery->whereHas('activities', function($query)
            use ($basicModuleId, $femaleAnswerId) {
                $query->forStudyComponent($basicModuleId)
                    ->completed()
                    ->whereHas('steps', function($query) use ($femaleAnswerId) {
                        $query->where('answer_text', 'a:1:{i:0;i:' . $femaleAnswerId . ';}');
                    });
        })
        ->get()
        ->count();
    }



    private function germanFemalePositive($returnQuery = false)
    {
        $basicModuleId = self::BASIC_ID;
        $symptomsModuleId = self::SYMPTOMS_ID;

        $countryModuleComponentId = self::QUESTION_COUNTRY;

        $femaleAnswerId = self::ANSWER_GENDER_FEMALE;


        $germanPositiveFemaleQuery = $this->germanPositiveParticipants(true)
            ->whereHas('activities', function($query)
            use ($basicModuleId, $femaleAnswerId) {
                $query->forStudyComponent($basicModuleId)
                    ->completed()
                    ->whereHas('steps', function($query) use ($femaleAnswerId) {
                        $query->where('answer_text', 'a:1:{i:0;i:' . $femaleAnswerId . ';}');
                    });
        });

        if ($returnQuery) {
            return $germanPositiveFemaleQuery;
        }

        return $germanPositiveFemaleQuery->get(['id', 'participant_id']);
    }



    /*
    * filters the participants by having answered yes to a question at least once
    */
    private function anweredYesToSymptom($participantsQuery, $questionId, $question2Id, $returnQuery = false)
    {
        $symptomsModuleId = self::SYMPTOMS_ID;
        $symptomsPositiveModuleId = self::SYMPTOMS_POSITIVE_ID;

        $participantsThatAnsweredYesQuery = $participantsQuery->whereHas('activities', function($query)
            use ($symptomsModuleId, $questionId) {
                $query->forStudyComponent($symptomsModuleId)
                    ->completed()
                    ->whereHas('steps', function($query) use ($questionId) {
                        $query->where('module_component_id', $questionId)->where('answer', 1);
                });
            })
            ->orWhereHas('activities', function($query) use ($symptomsPositiveModuleId, $question2Id) {
                $query->forStudyComponent($symptomsPositiveModuleId)
                    ->completed()
                    ->whereHas('steps', function($query) use ($question2Id) {
                        $query->where('module_component_id', $question2Id)->where('answer', 1);
                });
            });

        if ($returnQuery) {
            return $participantsThatAnsweredYesQuery;
        }

        return $participantsThatAnsweredYesQuery->get(['id', 'participant_id']);;
    }


    /*
    * filters the participants by having answered yes to a question in the basic module
    */
    private function anweredYesToCondition($participantsQuery, $questionId, $returnQuery = false)
    {
        $basicModuleId = self::BASIC_ID;

        $participantsThatAnsweredYesQuery = $participantsQuery->whereHas('activities', function($query)
            use ($basicModuleId, $questionId) {
                $query->forStudyComponent($basicModuleId)
                    ->completed()
                    ->whereHas('steps', function($query) use ($questionId) {
                        $query->where('module_component_id', $questionId)->where('answer', 1);
                });
            });

        if ($returnQuery) {
            return $participantsThatAnsweredYesQuery;
        }

        return $participantsThatAnsweredYesQuery->get(['id', 'participant_id'])->count();
    }


    private function participantAverageAge($participantsQuery, $totalOfParticipants)
    {

        $ageGroupAnswerIds = [
            '<30' => self::ANSWER_AGE_GROUP_30,
            '31-40' => self::ANSWER_AGE_GROUP_31_40,
            '41-50' => self::ANSWER_AGE_GROUP_41_50,
            '51-60' => self::ANSWER_AGE_GROUP_51_60,
            '61-70' => self::ANSWER_AGE_GROUP_61_70,
            '71-80' => self::ANSWER_AGE_GROUP_71_80,
            '>80' => self::ANSWER_AGE_GROUP_81,
        ];

        $ageGroupDistribution = [
            '<30' => $this->getAgeGroupCount((clone $participantsQuery), self::ANSWER_AGE_GROUP_30),
            '31-40' => $this->getAgeGroupCount((clone $participantsQuery), self::ANSWER_AGE_GROUP_31_40),
            '41-50' => $this->getAgeGroupCount((clone $participantsQuery), self::ANSWER_AGE_GROUP_41_50),
            '51-60' => $this->getAgeGroupCount((clone $participantsQuery), self::ANSWER_AGE_GROUP_51_60),
            '61-70' => $this->getAgeGroupCount((clone $participantsQuery), self::ANSWER_AGE_GROUP_61_70),
            '71-80' => $this->getAgeGroupCount((clone $participantsQuery), self::ANSWER_AGE_GROUP_71_80),
            '>80' => $this->getAgeGroupCount((clone $participantsQuery), self::ANSWER_AGE_GROUP_81)
        ];

        $ageAcumulator = 0;
        foreach ($ageGroupDistribution as $key => $participantsInAgeGroup) {

            $this->info('        ' . $key . ': ' . $participantsInAgeGroup . ' participants');

            $ageAcumulator += $participantsInAgeGroup * $this->ageGroupAverage($ageGroupAnswerIds[$key]);
        }

        return floor($ageAcumulator/$totalOfParticipants);

    }

    private function getAgeGroupCount($participantsQuery, $answerId)
    {
        $basicModuleId = self::BASIC_ID;
        $ageQuestionId = self::QUESTION_AGE_GROUP;

        return $participantsQuery->whereHas('activities', function($query)
            use ($basicModuleId, $ageQuestionId, $answerId) {
                $query->forStudyComponent($basicModuleId)
                    ->completed()
                    ->whereHas('steps', function($query) use ($ageQuestionId, $answerId) {
                        $query->where('module_component_id', $ageQuestionId)->where('answer_text', 'a:1:{i:0;i:' . $answerId  . ';}');
                });
            })
            ->get(['id'])
            ->count();
    }


    private function otherSymptomsExperienced($participantsQuery)
    {
        $symptomsModuleId = self::SYMPTOMS_ID;
        $symptomsPositiveModuleId = self::SYMPTOMS_POSITIVE_ID;
        $questionOtherSymptoms = self::QUESTION_OTHER_SYMPTOMS;
        $questionSymptomsPositiveOtherSymptoms = self::QUESTION_SYMPTOMS_POSITIVE_OTHER_SYMPTOMS;

        return $participantsQuery->whereHas('activities', function($query)
            use ($basicModuleId, $ageQuestionId, $answerId) {
                $query->forStudyComponent($symptomsModuleId)
                    ->completed()
                    ->whereHas('steps', function($query) use ($ageQuestionId, $answerId) {
                        $query->where('module_component_id', $questionOtherSymptoms)
                            ->whereNotNull('answer_text');
                });
            })
            ->get(['id'])
            ->count();
    }







    // Specific symptoms getters (Currently not being used)

    private function positiveThatExperimentedFatigue()
    {
        $feverQuestion = self::QUESTION_FATIGUE;
        $feverSymptomsPositiveQuestion = self::QUESTION_SYMPTOMS_POSITIVE_FATIGUE;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

    private function positiveThatExperimentedFever()
    {
        $feverQuestion = self::QUESTION_FEVER;
        $feverSymptomsPositiveQuestion = self::SYMPTOMS_POSITIVE_QUESTION_FEVER;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

    private function positiveThatExperimentedSnif()
    {
        $feverQuestion = self::QUESTION_SNIF;
        $feverSymptomsPositiveQuestion = self::QUESTION_SYMPTOMS_POSITIVE_SNIF;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

    private function positiveThatExperimentedChills()
    {
        $feverQuestion = self::QUESTION_CHILLS;
        $feverSymptomsPositiveQuestion = self::QUESTION_SYMPTOMS_POSITIVE_CHILLS;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

    private function positiveThatExperimentedCough()
    {
        $feverQuestion = self::QUESTION_COUGH;
        $feverSymptomsPositiveQuestion = self::QUESTION_SYMPTOMS_POSITIVE_COUGH;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

    private function positiveThatExperimentedVomit()
    {
        $feverQuestion = self::QUESTION_VOMIT;
        $feverSymptomsPositiveQuestion = self::QUESTION_SYMPTOMS_POSITIVE_VOMIT;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

    private function positiveThatExperimentedDiarrea()
    {
        $feverQuestion = self::QUESTION_DIARREA;
        $feverSymptomsPositiveQuestion = self::QUESTION_SYMPTOMS_POSITIVE_DIARREA;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

    private function positiveThatExperimentedHeadAche()
    {
        $feverQuestion = self::QUESTION_HEAD_ACHE;
        $feverSymptomsPositiveQuestion = self::QUESTION_SYMPTOMS_POSITIVE_HEAD_ACHE;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

    private function positiveThatExperimentedBodyAche()
    {
        $feverQuestion = self::QUESTION_BODY_ACHES;
        $feverSymptomsPositiveQuestion = self::QUESTION_SYMPTOMS_POSITIVE_BODY_ACHES;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

    private function positiveThatExperimentedSoreThroat()
    {
        $feverQuestion = self::QUESTION_SORE_THROAT;
        $feverSymptomsPositiveQuestion = self::QUESTION_SYMPTOMS_POSITIVE_SORE_THROAT;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

    private function positiveThatExperimentedShortnessOfBreath()
    {
        $feverQuestion = self::QUESTION_SHORTNESS_OF_BREATH;
        $feverSymptomsPositiveQuestion = self::QUESTION_SYMPTOMS_POSITIVE_SHORTNESS_OF_BREATH;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

    private function positiveThatExperimentedAlteredSenseOfSmell()
    {
        $feverQuestion = self::QUESTION_ALTERED_SENSE_OF_SMELL;
        $feverSymptomsPositiveQuestion = self::QUESTION_SYMPTOMS_POSITIVE_ALTERED_SENSE_OF_SMELL;

        $germanPositiveParticipantsQuery = $this->germanPositiveParticipants(true);

        return $this->anweredYesToSymptom($germanPositiveParticipantsQuery, $feverQuestion, $feverSymptomsPositiveQuestion);
    }

}
