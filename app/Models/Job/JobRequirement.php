<?php
class JobRequirement extends BaseModel {
    protected $table = 'job_requirements';
    protected $fillable = ['job_id', 'requirement_type', 'requirement_text', 'is_mandatory'];

    public function getRequirementsByJob($jobId){
        $this->db->query('SELECT * FROM job_requirements WHERE job_id = :job_id ORDER BY is_mandatory DESC, id ASC');
        $this->db->bind(':job_id', $jobId);
        return $this->db->resultSet();
    }

    public function addRequirement($jobId, $type, $text, $mandatory = false){
        $data = [
            'job_id' => $jobId,
            'requirement_type' => $type,
            'requirement_text' => $text,
            'is_mandatory' => $mandatory
        ];
        
        return $this->create($data);
    }

    public function updateRequirements($jobId, $requirements){
        // Delete existing requirements
        $this->db->query('DELETE FROM job_requirements WHERE job_id = :job_id');
        $this->db->bind(':job_id', $jobId);
        $this->db->execute();
        
        // Add new requirements
        foreach($requirements as $requirement){
            $this->addRequirement(
                $jobId,
                $requirement['type'],
                $requirement['text'],
                $requirement['mandatory'] ?? false
            );
        }
        
        return true;
    }

    public function getRequirementTypes(){
        return [
            'education' => 'Education',
            'experience' => 'Experience',
            'skills' => 'Skills',
            'certification' => 'Certification',
            'language' => 'Language',
            'other' => 'Other'
        ];
    }

    public function checkCandidateMatch($jobId, $candidateProfile){
        $requirements = $this->getRequirementsByJob($jobId);
        $matchScore = 0;
        $totalRequirements = count($requirements);
        
        if($totalRequirements == 0){
            return 100; // No requirements means 100% match
        }
        
        foreach($requirements as $requirement){
            $matches = $this->checkRequirementMatch($requirement, $candidateProfile);
            if($matches){
                $matchScore++;
            }
        }
        
        return ($matchScore / $totalRequirements) * 100;
    }

    private function checkRequirementMatch($requirement, $candidateProfile){
        $requirementText = strtolower($requirement->requirement_text);
        
        switch($requirement->requirement_type){
            case 'education':
                return $this->matchEducation($requirementText, $candidateProfile['education'] ?? '');
                
            case 'experience':
                return $this->matchExperience($requirementText, $candidateProfile['experience'] ?? '');
                
            case 'skills':
                return $this->matchSkills($requirementText, $candidateProfile['skills'] ?? '');
                
            case 'certification':
                return $this->matchCertification($requirementText, $candidateProfile['certifications'] ?? '');
                
            default:
                return false;
        }
    }

    private function matchEducation($requirement, $candidateEducation){
        $candidateEducation = strtolower($candidateEducation);
        
        // Simple keyword matching - can be enhanced with NLP
        $keywords = explode(' ', $requirement);
        foreach($keywords as $keyword){
            if(strpos($candidateEducation, $keyword) !== false){
                return true;
            }
        }
        
        return false;
    }

    private function matchExperience($requirement, $candidateExperience){
        $candidateExperience = strtolower($candidateExperience);
        
        // Extract years from requirement
        preg_match('/(\d+)\s*years?/', $requirement, $matches);
        $requiredYears = isset($matches[1]) ? intval($matches[1]) : 0;
        
        // Extract years from candidate experience
        preg_match('/(\d+)\s*years?/', $candidateExperience, $candidateMatches);
        $candidateYears = isset($candidateMatches[1]) ? intval($candidateMatches[1]) : 0;
        
        return $candidateYears >= $requiredYears;
    }

    private function matchSkills($requirement, $candidateSkills){
        $candidateSkills = strtolower($candidateSkills);
        $requiredSkills = explode(',', $requirement);
        
        $matchedSkills = 0;
        foreach($requiredSkills as $skill){
            $skill = trim($skill);
            if(strpos($candidateSkills, $skill) !== false){
                $matchedSkills++;
            }
        }
        
        // Return true if at least 50% of skills match
        return ($matchedSkills / count($requiredSkills)) >= 0.5;
    }

    private function matchCertification($requirement, $candidateCertifications){
        $candidateCertifications = strtolower($candidateCertifications);
        return strpos($candidateCertifications, $requirement) !== false;
    }
}
?>