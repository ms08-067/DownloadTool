<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

/**
 * Class manualdownloadlistListinfoTransformer
 *
 * @author sigmoswitch
 * @package App\Transformers
 */
class manualdownloadlistListinfoTransformer extends TransformerAbstract
{
    /**
     * Create a new transformer instance.
     *
     * @param $params
     */
    public function __construct($arrayfromroutings = [])
    {
        $this->fromRoutingsController = $arrayfromroutings;
    }

    /**
     * @author sigmoswitch
     * @param $resource
     * @return array
     */
    public function transform($resource)
    {
        $locale = $this->fromRoutingsController['locale'];
        // $employee_details = $this->fromRoutingsController['employee_details'];
        // $auth_user_permissions = $this->fromRoutingsController['auth_user_permissions'];
        $status_grouping_sort_order = $this->fromRoutingsController['status_grouping_sort_order'];
        /**dd($status_grouping_sort_order);*/

        $currently_downloading_aria2c = $this->fromRoutingsController['currently_downloading_aria2c'];
        /**dd($currently_downloading_aria2c);*/

        $number_of_pictures_table_show = false;
        $number_of_pictures_example_show = false;
        $number_of_pictures_new_show = false;
        $number_of_pictures_ready_show = false;

        if(isset($currently_downloading_aria2c[$resource->case_id])){
            /** go through each to determine if should display on page load so that it doesn't have a eye jerking effect for the client experience */
            foreach($currently_downloading_aria2c[$resource->case_id] as $generic_type_key => $currently_downloading_aria2c_type_details){
                if($generic_type_key == 'example'){
                    $number_of_pictures_example_show = true;
                }
                if($generic_type_key == 'new'){
                    $number_of_pictures_new_show = true;
                }
                if($generic_type_key == 'ready'){
                    $number_of_pictures_ready_show = true;
                }
            }

            if($number_of_pictures_example_show == true || $number_of_pictures_new_show == true || $number_of_pictures_ready_show == true){
                $number_of_pictures_table_show = true;
            }
        }else{
            $number_of_pictures_table_show = false;
            $number_of_pictures_example_show = false;
            $number_of_pictures_new_show = false;
            $number_of_pictures_ready_show = false;
        }
        
        $search_query_type_highlighting = $this->fromRoutingsController['search_query_type_highlighting'];
        /**dd($search_query_type_highlighting);*/

        $highlight_instruction_icon = '[ ! ]';
        if($resource->xml_jobinfoproduction != null){
            $resource->xml_jobinfoproduction = str_replace('"', "'", $resource->xml_jobinfoproduction);
            $resource->xml_jobinfoproduction = str_replace('[*~*]', "<br>", $resource->xml_jobinfoproduction);
        }else{
            $resource->xml_jobinfoproduction = '';
        }

        /**dump($resource->custom_hashtag);*/

        $reconstruct_hashtag_string = '';
        if($resource->custom_hashtag !== null){
            if(str_replace(" ", "", $resource->custom_hashtag) != ""){
                $exploded_hashtags = explode(" ", $resource->custom_hashtag);
                /**dd($exploded_hashtags);*/

                foreach($exploded_hashtags as $key => $value){

                    if($key != array_key_last($exploded_hashtags)){
                        $reconstruct_hashtag_string .= '#'.$value.' ';
                    }else{
                        $reconstruct_hashtag_string .= '#'.$value;
                    }
                }
            }
        }

        if (is_numeric($search_query_type_highlighting)) {
            $highlighted_job_instruction_based_on_search_query_type = $resource->xml_jobinfoproduction;
            $highlighted_job_title_based_on_search_query_type = $resource->xml_title_contents;
            $highlighted_job_assignees_based_on_search_query_type = $resource->assignees;
            $highlighted_job_tag_based_on_search_query_type = $reconstruct_hashtag_string;

            /** highlight nothing it is already filtered by jobid need to highlight partial searches */
            /** and if you did you need to becareful of the renaming since other parts of the javascript is using to perform function */
            if($search_query_type_highlighting !== ""){
                $exploded = explode(strtolower($search_query_type_highlighting), strtolower($resource->case_id));
                $remembering_upto_which_character_processed = 0;
                $highlighted_job_case_id_based_on_search_query_type = '';
                foreach($exploded as $generic_this_key => $string_details){
                    if($generic_this_key !== array_key_last($exploded)){
                        $string_length = strlen($string_details);
                        $search_string_length = strlen(strtolower($search_query_type_highlighting));
                        $original_string_cut_to_length = substr($resource->case_id, $remembering_upto_which_character_processed, $string_length);
                        /**dump($original_string_cut_to_length);*/
                        
                        /** shift places for string part */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                        $search_string_cut_to_length = substr($resource->case_id, $remembering_upto_which_character_processed, $search_string_length);
                        /**dd($search_string_cut_to_length);*/

                        $highlighted_job_case_id_based_on_search_query_type .= $original_string_cut_to_length;
                        $highlighted_job_case_id_based_on_search_query_type .= "<span class='highlight_span'>".$search_string_cut_to_length."</span>";

                        /** shift places for search string */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $search_string_length;                    
                    }else{
                        $string_length = strlen($string_details);
                        $search_string_length = strlen(strtolower($search_query_type_highlighting));
                        $original_string_cut_to_length = substr($resource->case_id, $remembering_upto_which_character_processed, $string_length);
                        /**dump($original_string_cut_to_length);*/
                        
                        /** shift places */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                        $highlighted_job_case_id_based_on_search_query_type .= $original_string_cut_to_length;
                    }
                }
            }else{
                $highlighted_job_case_id_based_on_search_query_type = $resource->case_id;
            }

            if($search_query_type_highlighting !== ""){
                $exploded = explode(strtolower($search_query_type_highlighting), strtolower($resource->xml_jobid_title));
                $remembering_upto_which_character_processed = 0;
                $highlighted_job_parent_case_id_based_on_search_query_type = '';
                foreach($exploded as $generic_this_key => $string_details){
                    if($generic_this_key !== array_key_last($exploded)){
                        $string_length = strlen($string_details);
                        $search_string_length = strlen(strtolower($search_query_type_highlighting));
                        $original_string_cut_to_length = substr($resource->xml_jobid_title, $remembering_upto_which_character_processed, $string_length);
                        /**dump($original_string_cut_to_length);*/
                        
                        /** shift places for string part */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                        $search_string_cut_to_length = substr($resource->xml_jobid_title, $remembering_upto_which_character_processed, $search_string_length);
                        /**dd($search_string_cut_to_length);*/

                        $highlighted_job_parent_case_id_based_on_search_query_type .= $original_string_cut_to_length;
                        $highlighted_job_parent_case_id_based_on_search_query_type .= "<span class='highlight_span'>".$search_string_cut_to_length."</span>";

                        /** shift places for search string */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $search_string_length;                    
                    }else{
                        $string_length = strlen($string_details);
                        $search_string_length = strlen(strtolower($search_query_type_highlighting));
                        $original_string_cut_to_length = substr($resource->xml_jobid_title, $remembering_upto_which_character_processed, $string_length);
                        /**dump($original_string_cut_to_length);*/
                        
                        /** shift places */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                        $highlighted_job_parent_case_id_based_on_search_query_type .= $original_string_cut_to_length;
                    }
                }
            }else{
                $highlighted_job_parent_case_id_based_on_search_query_type = $resource->xml_jobid_title;
            }

            /** highlight nothing it is already filtered by jobid */
        }else if(substr($search_query_type_highlighting, 0, 1) === '#'){
            $highlighted_job_instruction_based_on_search_query_type = $resource->xml_jobinfoproduction;
            $highlighted_job_title_based_on_search_query_type = $resource->xml_title_contents;
            $highlighted_job_assignees_based_on_search_query_type = $resource->assignees;
            $highlighted_job_case_id_based_on_search_query_type = $resource->case_id;
            $highlighted_job_parent_case_id_based_on_search_query_type = $resource->xml_jobid_title;

            /** highlight assignee */
            if($search_query_type_highlighting !== "#"){
                $exploded = explode(str_replace("#", "", strtolower($search_query_type_highlighting)), strtolower($reconstruct_hashtag_string));
                $remembering_upto_which_character_processed = 0;
                $highlighted_job_tag_based_on_search_query_type = '';
                foreach($exploded as $generic_this_key => $string_details){
                    if($generic_this_key !== array_key_last($exploded)){
                        $string_length = strlen($string_details);
                        $search_string_length = strlen(str_replace("#", "", strtolower($search_query_type_highlighting)));
                        $original_string_cut_to_length = substr($reconstruct_hashtag_string, $remembering_upto_which_character_processed, $string_length);
                        /**dump($original_string_cut_to_length);*/
                        
                        /** shift places for string part */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                        $search_string_cut_to_length = substr($reconstruct_hashtag_string, $remembering_upto_which_character_processed, $search_string_length);
                        /**dd($search_string_cut_to_length);*/

                        $highlighted_job_tag_based_on_search_query_type .= $original_string_cut_to_length;
                        $highlighted_job_tag_based_on_search_query_type .= "<span class='highlight_span'>".$search_string_cut_to_length."</span>";

                        /** shift places for search string */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $search_string_length;                    
                    }else{
                        $string_length = strlen($string_details);
                        $search_string_length = strlen(str_replace("#", "", strtolower($search_query_type_highlighting)));
                        $original_string_cut_to_length = substr($reconstruct_hashtag_string, $remembering_upto_which_character_processed, $string_length);
                        /**dump($original_string_cut_to_length);*/
                        
                        /** shift places */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                        $highlighted_job_tag_based_on_search_query_type .= $original_string_cut_to_length;
                    }
                }
            }else{
                $highlighted_job_tag_based_on_search_query_type = $reconstruct_hashtag_string;
            }            

            /** highlight tag (we cannot otherwise the multi tag function is lost)*/
        }else if(substr($search_query_type_highlighting, 0, 1) === '@'){
            $highlighted_job_instruction_based_on_search_query_type = $resource->xml_jobinfoproduction;
            $highlighted_job_title_based_on_search_query_type = $resource->xml_title_contents;
            $highlighted_job_tag_based_on_search_query_type = $reconstruct_hashtag_string;
            $highlighted_job_case_id_based_on_search_query_type = $resource->case_id;
            $highlighted_job_parent_case_id_based_on_search_query_type = $resource->xml_jobid_title;

            /** highlight assignee */
            if($search_query_type_highlighting !== "@"){
                $exploded = explode(str_replace("@", "", strtolower($search_query_type_highlighting)), strtolower($resource->assignees));
                $remembering_upto_which_character_processed = 0;
                $highlighted_job_assignees_based_on_search_query_type = '';
                foreach($exploded as $generic_this_key => $string_details){
                    if($generic_this_key !== array_key_last($exploded)){
                        $string_length = strlen($string_details);
                        $search_string_length = strlen(str_replace("@", "", strtolower($search_query_type_highlighting)));
                        $original_string_cut_to_length = substr($resource->assignees, $remembering_upto_which_character_processed, $string_length);
                        /**dump($original_string_cut_to_length);*/
                        
                        /** shift places for string part */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                        $search_string_cut_to_length = substr($resource->assignees, $remembering_upto_which_character_processed, $search_string_length);
                        /**dd($search_string_cut_to_length);*/

                        $highlighted_job_assignees_based_on_search_query_type .= $original_string_cut_to_length;
                        $highlighted_job_assignees_based_on_search_query_type .= "<span class='highlight_span'>".$search_string_cut_to_length."</span>";

                        /** shift places for search string */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $search_string_length;                    
                    }else{
                        $string_length = strlen($string_details);
                        $search_string_length = strlen(str_replace("@", "", strtolower($search_query_type_highlighting)));
                        $original_string_cut_to_length = substr($resource->assignees, $remembering_upto_which_character_processed, $string_length);
                        /**dump($original_string_cut_to_length);*/
                        
                        /** shift places */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                        $highlighted_job_assignees_based_on_search_query_type .= $original_string_cut_to_length;
                    }
                }
            }else{
                $highlighted_job_assignees_based_on_search_query_type = $resource->assignees;
            }
        }else if(substr($search_query_type_highlighting, 0, 1) === '!'){
            $highlighted_job_title_based_on_search_query_type = $resource->xml_title_contents;
            $highlighted_job_assignees_based_on_search_query_type = $resource->assignees;
            $highlighted_job_tag_based_on_search_query_type = $reconstruct_hashtag_string;
            $highlighted_job_case_id_based_on_search_query_type = $resource->case_id;
            $highlighted_job_parent_case_id_based_on_search_query_type = $resource->xml_jobid_title;



            /** and now if they want to do a multi word search then we have to do it as many as there are search terms .. */
            /** the filtering has already been taken care of by the controller */            
            /** highlight instruction tooltip */
            if($search_query_type_highlighting !== "!"){

                $multi_word_jobinforproduction_search = array_values(array_filter(explode('!', $search_query_type_highlighting)));
                /**dump($multi_word_jobinforproduction_search);*/

                $highlighted_job_instruction_based_on_search_query_type = [];

                foreach ($multi_word_jobinforproduction_search as $generic_inner_key => $value) {
                    $highlighted_job_instruction_based_on_search_query_type[$generic_inner_key] = '';

                    /** the problems is it will also scan the <br> tags too */
                    /** so how to avoid that? */
                    /** so that it also is not part of the result set we adjust the view */
                    /** we remove those and place them back afterwards */

                    $line_break_replacement_to_ommit_for_highlighting = str_replace("<br>", "[*~*]", strtolower($resource->xml_jobinfoproduction));
                    $exploded = explode(str_replace("!", "", trim(strtolower($value))), strtolower($line_break_replacement_to_ommit_for_highlighting));
                    /**dd($exploded);*/

                    $remembering_upto_which_character_processed = 0;
                    foreach($exploded as $generic_this_key => $string_details){
                        if($generic_this_key !== array_key_last($exploded)){
                            $string_length = strlen($string_details);
                            $search_string_length = strlen(str_replace("!", "", trim(strtolower($value))));
                            $original_string_cut_to_length = substr($line_break_replacement_to_ommit_for_highlighting, $remembering_upto_which_character_processed, $string_length);
                            /**dump($original_string_cut_to_length);*/
                            
                            /** shift places for string part */
                            $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                            $search_string_cut_to_length = substr($line_break_replacement_to_ommit_for_highlighting, $remembering_upto_which_character_processed, $search_string_length);
                            /**dd($search_string_cut_to_length);*/

                            $highlighted_job_instruction_based_on_search_query_type[$generic_inner_key] .= $original_string_cut_to_length;
                            $highlighted_job_instruction_based_on_search_query_type[$generic_inner_key] .= "<span>".$search_string_cut_to_length."</span>";

                            /** shift places for search string */
                            $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $search_string_length;                    
                        }else{
                            $string_length = strlen($string_details);
                            $search_string_length = strlen(str_replace("!", "", trim(strtolower($value))));
                            $original_string_cut_to_length = substr($line_break_replacement_to_ommit_for_highlighting, $remembering_upto_which_character_processed, $string_length);
                            /**dump($original_string_cut_to_length);*/
                            
                            /** shift places */
                            $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                            $highlighted_job_instruction_based_on_search_query_type[$generic_inner_key] .= $original_string_cut_to_length;
                        }
                    }
                }

                /** I need indevidual exploded arrays per each search keyword */
                $count_number_of_distinct_arrays = 0;
                foreach($highlighted_job_instruction_based_on_search_query_type as $compression_key => $compression_value){
                    $count_number_of_distinct_arrays++;
                    $name_of_the_variable = "searchword_".$compression_key;
                    $$name_of_the_variable = explode(" ", $compression_value);
                }
                /**dump('$count_number_of_distinct_arrays = ' .$count_number_of_distinct_arrays);*/

                $all_dynamic_variable_variable_concatenated = [];
                for ($i=0; $i < $count_number_of_distinct_arrays; $i++) { 
                    $name_of_the_variable = "searchword_".$i;
                    if(isset($$name_of_the_variable)){
                        foreach($$name_of_the_variable as $word_index => $word_value){
                            $all_dynamic_variable_variable_concatenated[$word_index][$i] = $word_value;
                        }
                    }
                }
                /**dump($all_dynamic_variable_variable_concatenated);*/

                $count_number_of_distinct_arrays_as_index = $count_number_of_distinct_arrays -1;
                /**dump($count_number_of_distinct_arrays_as_index);*/

                $final_commpressed_string = '';
                foreach($all_dynamic_variable_variable_concatenated as $generic_this_key => $word_index_value_set){
                    foreach($word_index_value_set as $inner_key => $word_value_to_check){
                        if($count_number_of_distinct_arrays_as_index > 0){
                            if($inner_key == 0){

                                $string_length_of_word_doing = 0;
                                $remembering_word_doing = '';

                                for ($i=0; $i <= $count_number_of_distinct_arrays_as_index; $i++) { 
                                    if ($i == 0){
                                        $string_length_of_word_doing = strlen($all_dynamic_variable_variable_concatenated[$generic_this_key][$i]);
                                        $remembering_word_doing = $all_dynamic_variable_variable_concatenated[$generic_this_key][$i];
                                    }else{
                                        if($i == $count_number_of_distinct_arrays_as_index){
                                            /** this is the final index */
                                            /**if a longer word has not been found then use the value from index 0*/
                                            if($string_length_of_word_doing > strlen($all_dynamic_variable_variable_concatenated[$generic_this_key][$i])){
                                                /** update the remembering_word_doing variable */
                                            }elseif($string_length_of_word_doing < strlen($all_dynamic_variable_variable_concatenated[$generic_this_key][$i])){
                                                /** we only want to longest word so do nothing in this case */
                                                $remembering_word_doing = $all_dynamic_variable_variable_concatenated[$generic_this_key][$i];
                                                /**$string_length_of_word_doing = strlen($all_dynamic_variable_variable_concatenated[$generic_this_key][$i]);*/
                                            }else{
                                                /** do not update the remembering_word_doing variable because the string length is the same */
                                            }
                                            /** finaly add it to the final_compressed_string variable */
                                            $final_commpressed_string .= $remembering_word_doing." ";
                                        }else{
                                            /** there are still search terms that need to be checked */
                                            if($string_length_of_word_doing > strlen($all_dynamic_variable_variable_concatenated[$generic_this_key][$i])){
                                                /** update the remembering_word_doing variable */
                                            }elseif($string_length_of_word_doing < strlen($all_dynamic_variable_variable_concatenated[$generic_this_key][$i])){
                                                $remembering_word_doing = $all_dynamic_variable_variable_concatenated[$generic_this_key][$i];
                                                $string_length_of_word_doing = strlen($all_dynamic_variable_variable_concatenated[$generic_this_key][$i]);
                                                /** we only want to longest word so do nothing in this case */
                                            }else{
                                                /** do not update the remembering_word_doing variable because the string length is the same */
                                            }
                                        }
                                    }
                                }
                            }
                        }else{
                            $final_commpressed_string .= $all_dynamic_variable_variable_concatenated[$generic_this_key][$inner_key]." ";
                        }
                    }
                }
                /**dd($final_commpressed_string);*/
                /**dump($highlighted_job_instruction_based_on_search_query_type);*/
                $highlighted_job_instruction_based_on_search_query_type = str_replace("<span>", "<span class='highlight_span'>", trim($final_commpressed_string));
                $highlighted_job_instruction_based_on_search_query_type = str_replace("[*~*]", "<br>", $highlighted_job_instruction_based_on_search_query_type);                
                /**dd($highlighted_job_instruction_based_on_search_query_type);*/
                $highlight_instruction_icon = "<span class='highlight_span'>[ ! ]</span>";
            }else{
                $highlighted_job_instruction_based_on_search_query_type = $resource->xml_jobinfoproduction;
            }
        }else{
            $highlighted_job_instruction_based_on_search_query_type = $resource->xml_jobinfoproduction;
            $highlighted_job_assignees_based_on_search_query_type = $resource->assignees;
            $highlighted_job_tag_based_on_search_query_type = $reconstruct_hashtag_string;
            $highlighted_job_case_id_based_on_search_query_type = $resource->case_id;
            $highlighted_job_parent_case_id_based_on_search_query_type = $resource->xml_jobid_title;

            /** highlight title */
            if($search_query_type_highlighting !== ""){
                $exploded = explode(strtolower($search_query_type_highlighting), strtolower($resource->xml_title_contents));
                $remembering_upto_which_character_processed = 0;
                $highlighted_job_title_based_on_search_query_type = '';
                foreach($exploded as $generic_this_key => $string_details){
                    if($generic_this_key !== array_key_last($exploded)){
                        $string_length = strlen($string_details);
                        $search_string_length = strlen(strtolower($search_query_type_highlighting));
                        $original_string_cut_to_length = substr($resource->xml_title_contents, $remembering_upto_which_character_processed, $string_length);
                        /**dump($original_string_cut_to_length);*/
                        
                        /** shift places for string part */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                        $search_string_cut_to_length = substr($resource->xml_title_contents, $remembering_upto_which_character_processed, $search_string_length);
                        /**dd($search_string_cut_to_length);*/

                        $highlighted_job_title_based_on_search_query_type .= $original_string_cut_to_length;
                        $highlighted_job_title_based_on_search_query_type .= "<span class='highlight_span'>".$search_string_cut_to_length."</span>";

                        /** shift places for search string */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $search_string_length;                    
                    }else{
                        $string_length = strlen($string_details);
                        $search_string_length = strlen(strtolower($search_query_type_highlighting));
                        $original_string_cut_to_length = substr($resource->xml_title_contents, $remembering_upto_which_character_processed, $string_length);
                        /**dump($original_string_cut_to_length);*/
                        
                        /** shift places */
                        $remembering_upto_which_character_processed = $remembering_upto_which_character_processed + $string_length;

                        $highlighted_job_title_based_on_search_query_type .= $original_string_cut_to_length;
                    }
                }
            }else{
                $highlighted_job_title_based_on_search_query_type = $resource->xml_title_contents;
            }
        }


        /**dd($status_grouping_sort_order);*/

        /**dd($resource);*/
        // {#1503
        //   +"id": "794"
        //   +"case_id": "11444226"
        //   +"state": "check"
        //   +"try": "0"
        //   +"time": "1601462766"
        //   +"from": "0"
        //   +"has_mapping_name": null
        //   +"created_at": "2020-09-30 17:46:06"
        //   +"updated_at": "2020-12-14 13:41:23"
        //   +"last_updated_by": "661"
        //   +"assignees": null
        //   +"custom_delivery_time": "2020-12-14 04:00:00"
        //   +"custom_color": null
        //   +"custom_internal_notes": null
        //   +"custom_job_star_rating": null
        //   +"custom_job_star_rating_comment": null
        //   +"custom_hashtag": null
        //   +"archived_case": "1"
        //   +"preview_req": "2"
        //   +"xml_deliverytime_contents_formatted": "2020-10-01 04:00:00"
        //   +"last_updated_by_name": "Nickolas Vandenbroucke"
        //   +"xml_title_contents": "InDesign_Marktkauf - 30.09.2020 12:45"
        //   +"xml_jobid_title": ""
        //   +"xml_jobinfo": "!!!!BITTE IMMER im IDML-Format (CS4 oder älter) abspeichern!!!!<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>Bitte die Daten immer genau so benennen wie sie eingegangen sind!!"
        //   +"xml_jobinfoproduction": "INDESIGN TEAM<br><br><br><br><br><br>- Naming data as received"
        //   +"expected_delivery_date_coalesce": "2020-12-14 04:00:00"
        //   +"custom_delivery_time_original": "2020-12-14 04:00:00"
        //   +"xml_deliverytime_contents_formatted_original": "2020-10-01 04:00:00"
        //   +"xml_deliverytime_contents_formatted_sub_2_original": "2020-10-01 02:00:00"
        //   +"status_of_case": "check"
        //   +"vuf_state": "check"
        //   +"vuf_initiator": null
        //   +"vuf_updated_at": "2020-12-14 13:33:56"
        //   +"vuf_created_at": "2020-10-01 11:50:38"
        //   +"vuf_move_to_jobfolder": "0"
        //   +"vuf_move_to_jobfolder_tries": "0"
        //   +"vuf_sending_to_s3": "0"
        //   +"vuf_sending_to_s3_tries": "0"
        //   +"vuf_pid": null
        //   +"number": 1
        //   +"last_updated_sortorder": 1
        //   +"created_at_sortorder": 51
        //   +"expected_delivery_date_coalesce_sortorder": 1
        //   +"example_file_count": null
        //   +"new_file_count": "3"
        // }


        /**dd($resource);*/

        switch ($locale) {
            case 'en':
            $format = CASE_EN_DATE_FORMAT;
            $last_updated_format = CASE_EN_LAST_UPDATED_DATE_FORMAT;
            $last_updated_format_short = CASE_EN_LAST_UPDATED_DATE_FORMAT_SHORT;
            break;
            case 'de':
            $format = CASE_DE_DATE_FORMAT;
            $last_updated_format = CASE_DE_LAST_UPDATED_DATE_FORMAT;
            $last_updated_format_short = CASE_DE_LAST_UPDATED_DATE_FORMAT_SHORT;
            break;
            case 'vi':
            $format = CASE_VI_DATE_FORMAT;
            $last_updated_format = CASE_VI_LAST_UPDATED_DATE_FORMAT;
            $last_updated_format_short = CASE_VI_LAST_UPDATED_DATE_FORMAT_SHORT;
            break;
            default:
            $format = CASE_DEFAULT_DATE_FORMAT;
            $last_updated_format = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT;
            $last_updated_format_short = CASE_DEFAULT_LAST_UPDATED_DATE_FORMAT_SHORT;
        }

        if(isset($resource->time)){
            $time = Carbon::createFromTimestamp($resource->time);
            $time = Carbon::parse($time)->format($format. ' H:i:s');
            $created_at_timestamp = $resource->time;
        }else{
            $time = '';
            $created_at_timestamp = '';
        }

        // if(isset($resource->calendar_date)){
        //  $calendar_date = Carbon::createFromFormat('Y-m-d', $resource->calendar_date);
        //  $calendar_date = Carbon::parse($calendar_date)->format($format);
        // }else{
        //  $calendar_date = '';
        // }

        if(isset($resource->updated_at)){
            $last_updated = Carbon::createFromFormat('Y-m-d H:i:s', $resource->updated_at);
            $last_updated = Carbon::parse($last_updated)->format($last_updated_format);
        }else{
            $last_updated = '';
        }



        if(isset($resource->xml_deliverytime_contents)){
            $xml_deliverytime_contents = Carbon::createFromTimestamp($resource->xml_deliverytime_contents);
            $xml_deliverytime_contents = '<span data-ts="'.$resource->xml_deliverytime_contents.'">'.Carbon::parse($xml_deliverytime_contents)->format($last_updated_format_short).'</span>';
        }else{
            $xml_deliverytime_contents = '';
        }





        if(isset($resource->custom_delivery_time)){
            $custom_delivery_time = Carbon::createFromFormat('Y-m-d H:i:s', $resource->custom_delivery_time);
            $custom_delivery_time = '<span data-ts="'.$resource->custom_delivery_time.'">'.Carbon::parse($custom_delivery_time)->format($last_updated_format_short).'</span>';
        }else{
            $custom_delivery_time = '';
        }


        if(isset($resource->expected_delivery_date_coalesce)){
            $expected_delivery_date_coalesce = Carbon::createFromFormat('Y-m-d H:i:s', $resource->expected_delivery_date_coalesce);

            $expected_delivery_date_coalesce = '<span data-ts="'.$resource->expected_delivery_date_coalesce.'">'.Carbon::parse($expected_delivery_date_coalesce)->format($last_updated_format_short).'</span>';

            /** removed to make use of the expected_delivery_date_coalesce_sortorder from the Controller (custom sorting for organizational purposes) */
            //$expected_delivery_date_coalesce_sort_order = Carbon::createFromFormat('Y-m-d H:i:s', $resource->expected_delivery_date_coalesce)->timestamp;

        }else{
            $expected_delivery_date_coalesce = '';
            /** removed to make use of the expected_delivery_date_coalesce_sortorder from the Controller (custom sorting for organizational purposes) */
            //$expected_delivery_date_coalesce_sort_order = 0;
        }





        /** In case someone wants to change it. */
        /** The only change they can do is to change the hours to earlier (negative number) or later (positive number). */
        /**  If the timespan is positive display it as bold red text, negative as green. It should be easy to get that red is bad and green is good */

        /** to be able to color it like that we need the original sub hour date time */

        if(isset($resource->custom_delivery_time_original)){
            $custom_delivery_time_original = Carbon::createFromFormat('Y-m-d H:i:s', $resource->custom_delivery_time_original);

            //$custom_delivery_time_original = '<span data-ts="'.$resource->custom_delivery_time_original.'">'.Carbon::parse($custom_delivery_time_original)->format($last_updated_format_short).'</span>';
        }else{
            $custom_delivery_time_original = '';
        }

        if(isset($resource->xml_deliverytime_contents_formatted_sub_2_original)){
            $xml_deliverytime_contents_formatted_sub_2_original = Carbon::createFromFormat('Y-m-d H:i:s', $resource->xml_deliverytime_contents_formatted_sub_2_original);

            //$xml_deliverytime_contents_formatted_sub_2_original = '<span data-ts="'.$resource->xml_deliverytime_contents_formatted_sub_2_original.'">'.Carbon::parse($xml_deliverytime_contents_formatted_sub_2_original)->format($last_updated_format_short).'</span>';
        }else{
            $xml_deliverytime_contents_formatted_sub_2_original = '';
        }


        $color_it = null;

        if($custom_delivery_time_original != ''){
            /** means there is a custom time */
            /** check if it is later or before the xml_deliverytime_contents_formatted_sub_2_original */

            if($custom_delivery_time_original->greaterThan($xml_deliverytime_contents_formatted_sub_2_original)){
                $color_it = 'font-weight: 900; color: red;';
            }else{
                $color_it = 'font-weight: 900; color: green;';
            }

            if($custom_delivery_time_original->equalTo($xml_deliverytime_contents_formatted_sub_2_original)){
                $color_it = 'color: #676a6c;';
            }
        }else{
            $color_it = 'color: #676a6c;';
        }






        // if($resource->user_status === 1){
        //  /** active */
        //  $user_status_icon = '<i class="fa fa-toggle-on text-navy"></i>';
        //  $title = 'Active';
        // }elseif ($resource->user_status === 2){
        //  /** inactive */
        //  $user_status_icon = '<i class="fa fa-toggle-off text-danger"></i>';
        //  $title = 'Inactive';
        // }elseif ($resource->user_status === 3) {
        //  /** resignation */
        //  $user_status_icon = '<i class="fa fa-sign-out text-danger"></i>';
        //  $title = 'Resigned';
        // }elseif ($resource->user_status === 4){
        //  /** terminated */
        //  $user_status_icon = '<i class="fa fa-times text-danger"></i>';
        //  $title = 'Terminated';
        // }elseif ($resource->user_status === 5){
        //  /** terminated */
        //  $user_status_icon = '<i class="fa fa-pause text-warning"></i>';
        //  $title = 'Paused';
        // }


        $edit_disabled_or_not = '';
        // $edit_URL = route('AjaxManagedownloadlistTabsController.getManagedownloadlist_TabAccounting_edit_downloadlist_CB', ['id' => Crypt::encryptString($resource->id)]);
        // $delete_URL = route('downloadlist.delete_downloadlist', ['id' => Crypt::encryptString($resource->id)]);  
        // $deleteable_replaceable_or_not = 'deleteable="true"';
        // $delete_button = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a name="delete_advance_payment_request_'.$resource->id.'" href="'.$delete_URL.'" class="btn-danger btn btn-xs btn-delete" '.$deleteable_replaceable_or_not.'><i class="fa fa-trash"></i>&nbsp;&nbsp;Delete</a>';
        // $download_URL = route('downloadlist.download_downloadlist', ['id' => Crypt::encryptString($resource->id)]);
        // $download_button = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a name="download_downloadlist_'.$resource->id.'" href="'.$download_URL.'" class="btn-info btn btn-xs" target="_blank"><i class="fa fa-download"></i>&nbsp;&nbsp;Download</a>';


        $edit_URL = '';
        $delete_URL = '';
        $deleteable_replaceable_or_not = 'deleteable="true"';
        $delete_button = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a name="delete_advance_payment_request_'.$resource->id.'" href="'.$delete_URL.'" class="btn-danger btn btn-xs btn-delete" '.$deleteable_replaceable_or_not.'><i class="fa fa-trash"></i>&nbsp;&nbsp;Delete</a>';
        $download_URL = '';
        $download_button = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a name="download_downloadlist_'.$resource->id.'" href="'.$download_URL.'" class="btn-info btn btn-xs" target="_blank"><i class="fa fa-download"></i>&nbsp;&nbsp;Download</a>';

        /** if the working shift is already in use then it is not possible to delete it and so it should be greyed out */

        $actions_available = '<div style="padding-top: 3px;"><a id="cb_edit_downloadlist" href="'.$edit_URL.'" class="ajax btn-white btn btn-xs" title="Edit Delivery Time" '.$edit_disabled_or_not.'><i class="fa fa-edit"></i>&nbsp;&nbsp;Edit Delivery Time</a></div>';

        //$actions_available = '';
        //if (in_array("READ organization", $auth_user_permissions)) {
        //  $link_to_employee = '<a class="button" href="/employeeprofile/'.$resource->fk_user_id.'"><span class="">'.$resource->fullname.'</span></a>';
        // }else{
        //  $link_to_employee = '<span class="">'.$resource->fullname.'</span>';
        // }




        $number_of_pictures = '<div style="display: inline-block">';

        if(isset($resource->example_file_count) && (int) $resource->example_file_count > 0){
            $number_of_pictures .= '<span>'.$resource->example_file_count.'</span>';
        }else{
            $number_of_pictures .= '    ';
        }
        $number_of_pictures .= ' | ';
        if(isset($resource->new_file_count) && (int) $resource->new_file_count > 0){
            $number_of_pictures .= '<span>'.$resource->new_file_count.'</span>';   
        }else{
            $number_of_pictures .= '    ';
        }
        $number_of_pictures .= ' | ';
        if(isset($resource->ready_file_count) && (int) $resource->ready_file_count > 0){
            $number_of_pictures .= '<span>'.$resource->ready_file_count.'</span>';   
        }else{
            $number_of_pictures .= '    ';
        }
        $number_of_pictures .= '</div>';




        /** now we disable the ability to change the colors manually so we can safely ignore the custom_color column from the db */

        if($resource->custom_color == null){
            $custom_color_rgb = '#FFFFFF';
        }else{
            $rgb = $this->hex2rgb($resource->custom_color);
            $custom_color_rgb = 'rgb('.$rgb['red'].', '.$rgb['green'].', '.$rgb['blue'].')';
            /**dd($custom_color_rgb);*/
        }

        /** follow this coloring convention */
        /** but we can use the colors provided for some statuses from the base.php array */

        /** to have the system auto color the row based on the status */
        /** to disable the manual color input field and column all together */
        #EB1500 RED
        #FFFD00 YELLOW
        #60BC1F GREEN

        //green - job is any status which indicated the job has been finished
        //yellow - the job status is in "feedback"
        //red - the expected delivery time is today

        foreach($status_grouping_sort_order as $job_status_key => $job_status_details){
            /**dd($job_status_details);*/
            if($resource->status_of_case == $job_status_details['name']){
                $rgb = $this->hex2rgb($job_status_details['row_color']);
                $custom_color_rgb = 'rgb('.$rgb['red'].', '.$rgb['green'].', '.$rgb['blue'].')';
                break;
            }
        }


        /** the expected delivery time is today make the row RED */
        $carbon_startOftoday = Carbon::now()->startOfDay();
        $carbon_endOftoday = Carbon::now()->endOfDay();
        /**dump($carbon_startOftoday);*/
        /**dump($carbon_endOftoday);*/


        /** if the status is any of the ones that need to be colored then overide the red row coloring */
        /** */
        //        $array_of_cases_to_override_today_row_color = ['downloaded', 'in progress', 'check'];
        //
        //        /**dump($custom_delivery_time_original);*/
        //        if($custom_delivery_time_original != ''){
        //            $check_deliverytime_is_today = $custom_delivery_time_original->between($carbon_startOftoday, $carbon_endOftoday);
        //            /**dd($check_deliverytime_is_today);*/
        //
        //            if($check_deliverytime_is_today && in_array($resource->status_of_case, $array_of_cases_to_override_today_row_color)){
        //                $rgb = $this->hex2rgb('#EB1500');
        //                $custom_color_rgb = 'rgb('.$rgb['red'].', '.$rgb['green'].', '.$rgb['blue'].')';
        //            }else{
        //                /** we do not overwrite the color of the row... */
        //            }
        //        }else{
        //            /** switch to the other */
        //            $check_deliverytime_is_today = $xml_deliverytime_contents_formatted_sub_2_original->between($carbon_startOftoday, $carbon_endOftoday);
        //            /**dd($check_deliverytime_is_today);*/
        //
        //            if($check_deliverytime_is_today && in_array($resource->status_of_case, $array_of_cases_to_override_today_row_color)){
        //                $rgb = $this->hex2rgb('#EB1500');
        //                $custom_color_rgb = 'rgb('.$rgb['red'].', '.$rgb['green'].', '.$rgb['blue'].')';
        //            }else{
        //                /** we do not overwrite the color of the row... */
        //            }
        //        }
        //
        //        /** change white to off white */
        //        if($custom_color_rgb == 'rgb(255, 255, 255)'){
        //            $rgb = $this->hex2rgb('#f3f3f4');
        //            $custom_color_rgb = 'rgb('.$rgb['red'].', '.$rgb['green'].', '.$rgb['blue'].')';
        //        }


        $item_status_grouping_sort_order = 0;

        foreach($status_grouping_sort_order as $job_status_key => $job_status_details){
            /**dd($job_status_details);*/
            if($resource->status_of_case == $job_status_details['name']){
                $item_status_grouping_sort_order = $job_status_details['sort_order'];
                break;
            }
        }



        if($resource->custom_job_star_rating_comment != ""){
            $display_edit_custom_job_star_rating_comment = '<i name="edit_custom_star_rating_comment_'.$resource->number.'" style="margin-left: 8px; font-size: 17px; cursor: pointer;" class="fa fa-edit"></i>';
        }else{
            $display_edit_custom_job_star_rating_comment = '<i name="edit_custom_star_rating_comment_'.$resource->number.'" style="display: none; margin-left: 8px; font-size: 17px; cursor: pointer;" class="fa fa-edit"></i>';
        }




        $preview_req_check_box_checked = '';
        if($resource->preview_req == 1){
            $preview_req_check_box_checked = 'checked';
        }

        if($resource->assignees !== null){
            if(str_replace(" ", "", $resource->assignees) == ''){
                $preview_req_check_box_disabled = 'disabled';
            }else{
                $preview_req_check_box_disabled = '';
            }
        }else{
            $preview_req_check_box_disabled = 'disabled';
        }

        if($resource->status_of_case == 'new'){
            $redownload_button_disable_enable = 'disabled';
            $redownload_button = '';
        }else{
            $redownload_button_disable_enable = '';
            $redownload_URL = route('manualdownloadlist.postModifyForcefullyReManualdownloadforJob', ['id' => Crypt::encryptString($resource->case_id)]);
            $redownload_button = '<a name="redownload_manualdownloadlist_'.$resource->number.'" href="'.$redownload_URL.'" data-case_id="'.$resource->case_id.'" class="btn-info btn-xs" target="_blank" style="font-size: 0.8vw" '.$redownload_button_disable_enable.'><i class="fa fa-download"></i>&nbsp;&nbsp;Re-Download</a>';
        }

        return [
            'idx' => $resource->number,
            'case_id' => $resource->case_id,
            'case_id_display' => $highlighted_job_case_id_based_on_search_query_type,
            'encrypted_case_id' => Crypt::encryptString($resource->case_id),
            'xml_title_contents' => $highlighted_job_title_based_on_search_query_type,
            'xml_jobid_title' => ($resource->xml_jobid_title != null) ? $resource->xml_jobid_title : '',
            'xml_jobid_title_display' => $highlighted_job_parent_case_id_based_on_search_query_type,
            'job_status' => $resource->status_of_case,
            'created_at' => $time,
            'created_at_timestamp' => $created_at_timestamp,
            'job_from' => $redownload_button,
            'custom_job_star_rating_comment' => $resource->custom_job_star_rating_comment,
            'rating' => $resource->custom_job_star_rating,
            //'display_edit_custom_job_star_rating_comment' => $display_edit_custom_job_star_rating_comment,
            'tags' => $highlighted_job_tag_based_on_search_query_type,

            'instructions_col' => $highlight_instruction_icon,
            'xml_jobinfo' => ($resource->xml_jobinfo != null) ? $resource->xml_jobinfo : '',
            'xml_jobinfoproduction' => $highlighted_job_instruction_based_on_search_query_type,

            'preview_col' => '<input type="checkbox" name="edit_'.$resource->case_id.'" data-case_id="'.$resource->case_id.'" '.$preview_req_check_box_checked.' '.$preview_req_check_box_disabled.'>',
            
            'output_files_col' => '',
            'output_number_of_pictures_expected' => $resource->custom_output_expected, /** manual created amount of files need to send */
            'output_number_of_pictures_real' => $resource->vuf_custom_output_real, /** the real amount of files sent to s3 using the tool uploader */


            'created_at_sortorder' => $resource->created_at_sortorder,
            'last_updated_by' => $resource->last_updated_by_name,
            'last_updated' => $last_updated,
            'last_updated_sortorder' => (int) $resource->last_updated_sortorder,

            //'actions' => $actions_available,
            'assignees' => $highlighted_job_assignees_based_on_search_query_type,
            
            'color_it' => $color_it,
            'delivery_time' => $expected_delivery_date_coalesce,
            //'expected_delivery_date_coalesce_sortorder' => (int) $expected_delivery_date_coalesce_sort_order,
            'expected_delivery_date_coalesce_sortorder' => $resource->expected_delivery_date_coalesce_sortorder,
            
            'number_of_pictures' => '',
            'number_of_pictures_example' => $resource->example_file_count,
            'number_of_pictures_new' => $resource->new_file_count,
            'number_of_pictures_ready' => $resource->ready_file_count,

            /** for each of the case id types if they are currently downloading have the client open up those details */
            'number_of_pictures_table_show' => $number_of_pictures_table_show,
            'number_of_pictures_example_show' => $number_of_pictures_example_show,
            'number_of_pictures_new_show' => $number_of_pictures_new_show,
            'number_of_pictures_ready_show' => $number_of_pictures_ready_show,


            'custom_color_rgb' => $custom_color_rgb,
            'custom_row_color_input' => '',

            'custom_row_color' => $resource->custom_color,
            'status_grouping' => $resource->status_of_case,
            'status_grouping_sort_order' => $item_status_grouping_sort_order,
            'internal_notes' => $resource->custom_internal_notes,

            'expected_delivery_time_custom_grouping' => $resource->expected_delivery_time_custom_grouping,
        ];      
    }


    public function hex2rgb($colour)
    {
        if ($colour[0] == '#') {
            $colour = substr($colour, 1);
        }
        if (strlen($colour) == 6) {
            list($r, $g, $b) = array($colour[0].$colour[1], $colour[2].$colour[3], $colour[4].$colour[5]);
        } elseif (strlen($colour) == 3) {
            list($r, $g, $b) = array($colour[0].$colour[0], $colour[1].$colour[1], $colour[2].$colour[2]);
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        /**dump($r);*/
        /**dump($g);*/
        /**dump($b);*/
        return array( 'red' => $r, 'green' => $g, 'blue' => $b );
    }

}
