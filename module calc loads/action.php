<?php
  $answer = json_decode(file_get_contents('php://input'), true);

  /*loads base constants*/
  define("roof", 1.1);
  define("lathing", 1.3);
  define("sling", 1.3);
  define("snow", 1.4);

 /*base variabls housetop*/
  $housetop = $answer["housetop"];
  $roof_pitch = $housetop["roof_pitch"];
  $rafters_width = $housetop["rafters_width"];
  $roof_width = $housetop["roof_width"];
  $rafters_step = $housetop["rafters_step"];
  $roof_power = $housetop["roof_power"];
  $snow_power = $housetop["snow_power"];
  $lathing = $housetop["lathing"];
  $lathing_step = $lathing["lathing_step"];
  $lathing_b = $lathing["lathing_b"];
  $lathing_h = $lathing["lathing_h"];

  if(isset($answer['housetop']))
  {
   /* $housetop = $answer['housetop'];*/
    $resultArray["calcLoads"] = calcLoads();
    echo json_encode($resultArray);
  }
  else
  {
      echo "Введенные данные некорректны";
  }


  function calcLoads() {

    /*base global variabls*/
    global $roof_pitch;
    global $rafters_width;
    global $roof_width;
    global $rafters_step;
    global $roof_power;
    global $snow_power;
    global $lathing;
    global $lathing_step ;
    global $lathing_b;
    global $lathing_h;

    /*------------------------additional variable--------------------------------- */
    $lathing_section = $lathing_b * $lathing_h * pow(10, -4);
    /*-----------------------------------------------------------------------------*/

    /*load calculation*/
    $arrayLoads = calculationLoad($lathing_section);
    /*---------------*/

    /*lathing calculation*/
    $arrayLathing = calculationLathing($arrayLoads);
    /*---------------*/

    /*------------------------additional variable--------------------------------- */
    $AB = round(($roof_width / cos($roof_pitch * M_PI / 180)) , 2);
    $N = round(0.5 * $arrayLoads["all_power_calc"] * cos($roof_pitch * M_PI / 180) * sin($roof_pitch * M_PI / 180) * $AB , 0);
    $L = round(0.5 * $arrayLoads["all_power_calc"] * $roof_width);
    /*-----------------------------------------------------------------------------*/

    /*Strop calculation  */
    $arrayStrop["deflection"] = $AB *100/200;
    $arrayStrop["Ru"] = 130;
    $arrayStrop["Moment_all"] = ($arrayLoads["all_power_calc"] * pow($roof_width,2))/8;
    $arrayStrop["Wtr"] = $arrayStrop["Moment_all"] * 100 / $arrayStrop["Ru"];
    $arrayStrop["Jtr"] = (5 * ($arrayLoads["all_power_norm"] / 100) * (pow(($roof_width * 100),3) * $AB * 100)) /
                         (384 * pow(10, 5) * $arrayStrop["deflection"] * (cos($roof_pitch * M_PI / 180)));
    $arrayStrop["Htr_fix"] = sqrt(6 * $arrayStrop["Wtr"] / $rafters_width);
    $arrayStrop["Htr_deflection"] = pow(((12 *  $arrayStrop["Jtr"]) / $rafters_width) , 0.333334);
    $arrayStrop["Btr"] = pow((($arrayStrop["Moment_all"] * 100 * $lathing_step * 100) /
                              (26.8 * $arrayStrop["Ru"] * max($arrayStrop["Htr_fix"], $arrayStrop["Htr_deflection"]))) , 0.333334);
    $arrayStrop["H_sort"] = hSort($arrayStrop["Htr_fix"], $arrayStrop["Htr_deflection"]);
    $arrayStrop["H_posible"] = 25;
    $arrayStrop["flexibility"] = $AB * 100 / (0.289 * $arrayStrop["H_posible"]);
    $arrayStrop["K_prod_flexibility"] = ($arrayStrop["flexibility"] <= 70) ? 1 - 0.8 * pow(($arrayStrop["flexibility"] / 100), 2) : 3000 / pow($arrayStrop["flexibility"], 2);
    $arrayStrop["K_x"] = (1 - $N / ($arrayStrop["K_prod_flexibility"] * $arrayStrop["Ru"] * $rafters_width * $arrayStrop["H_posible"]));
    $arrayStrop["Moment_compres"] = $arrayStrop["Moment_all"] / $arrayStrop["K_x"];
    $arrayStrop["lathing_section"] = $lathing_section;

    /*------------------------additional variable--------------------------------- */
    $M = round($arrayStrop["Moment_all"]);
    /*-----------------------------------------------------------------------------*/

    /*Tension calculation*/
    $arrayTension["strain_flex"] = $N / ($rafters_width * $arrayStrop["H_posible"]) + ($arrayStrop["Moment_compres"] * 100) /
                                      (($rafters_width * pow($arrayStrop["H_posible"], 2)) / 6);
    $arrayTension["deflection_rafters"] = (5 * $arrayLoads["all_power_norm"] / 100 * pow(($roof_width * 100), 3) * $AB * 100) /
                                          (384 * pow(10, 5) * ($rafters_width * pow($arrayStrop["H_posible"], 3)) /
                                           12 * cos($roof_pitch * M_PI / 180));
    $arrayTension["Tension_chipping"] = (3 * $L * cos($roof_pitch * M_PI / 180)) / ( 2 * $rafters_width * $arrayStrop["H_posible"]);


    /*Calculation conditions */
    $arrayCondition["first_condition"] = ($arrayStrop["Ru"] / $arrayTension["strain_flex"]) > 1 ? "Условие выполнено!":"Условие не выполнено!";
    $arrayCondition["second_condition"] = ($arrayStrop["deflection"] / $arrayTension["deflection_rafters"]) > 1 ? "Условие выполнено!":"Условие не выполнено!";
    $arrayCondition["third_condition"] = (16 / $arrayTension["Tension_chipping"]) > 1 ? "Условие выполнено!":"Условие не выполнено!";

    $resultArray["Strop"] = $arrayStrop;
    $resultArray["loads"] = $arrayLoads;
    $resultArray["lathing"] = $arrayLathing;
    $resultArray["Tension"] = $arrayTension;
    $resultArray["Condition"] = $arrayCondition;

    return $resultArray;
  }

  function hSort($fix, $deflection) {
  if(max($fix, $deflection) <= 7.5) {
    return 7.5;
  } else {
      if((max($fix, $deflection) > 7.5) && (max($fix, $deflection) <= 10)) {
        return 10;
      } else {
        if((max($fix, $deflection) > 10) && (max($fix, $deflection) <= 12.5)) {
          return 12.5;
        } else {
          if((max($fix, $deflection) > 12.5) && (max($fix, $deflection) <= 15)) {
            return 15;
          } else {
            if((max($fix, $deflection) > 15) && (max($fix, $deflection) <= 17)) {
              return 17.5;
            } else {
              if((max($fix, $deflection) > 17.5) && (max($fix, $deflection) <= 20)) {
                return 20;
              } else {
                if(max($fix, $deflection) <= 22.5) {
                  22.5;
                } else {
                  25;
                }
              }
            }
          }
        }
      }
    }
  }

  function calculationLoad($lathingSection) {
    global $roof_power;
    global $roof_pitch;
    global $lathing_step;
    global $rafters_width;
    global $snow_power;
    global $rafters_step;
    $array["roof_power_norm"] = ($roof_power/cos( $roof_pitch * M_PI/180)) *  $rafters_step;
    $array["lathing_power_norm"] = ($lathingSection*500)/( $lathing_step*cos( $roof_pitch*M_PI/180)) *  $rafters_step;
    $array["rafters_step_power_norm"] =  $rafters_width;
    $array["snow_power_norm"] = ( $roof_pitch <= 25) ?  $snow_power *  $rafters_step :  $snow_power
                                                                       * ((60 -  $roof_pitch)*0.0286)
                                                                       *  $rafters_step;
    $array["all_power_norm"] = $array["roof_power_norm"] + $array["lathing_power_norm"]
                                                                   + $array["rafters_step_power_norm"]
                                                                   + $array["snow_power_norm"];

    $array["roof_power_calc"] = $array["roof_power_norm"] * roof;
    $array["lathing_power_calc"] = $array["lathing_power_norm"] * lathing;
    $array["rafters_step_power_calc"] = $array["rafters_step_power_norm"] * sling;
    $array["snow_power_calc"] = $array["snow_power_norm"] * snow;
    $array["all_power_calc"] = $array["roof_power_calc"] + $array["lathing_power_calc"]
                                                                   + $array["rafters_step_power_calc"]
                                                                   + $array["snow_power_calc"];
    return $array;
  }


  function calculationLathing($arrayLoads) {
    global $roof_pitch;
    global $rafters_width;
    global $roof_width;
    global $rafters_step;
    global $roof_power;
    global $snow_power;
    global $lathing;
    global $lathing_step ;
    global $lathing_b;
    global $lathing_h;
    $array["Length_power"] = ($arrayLoads["roof_power_calc"] + $arrayLoads["lathing_power_calc"]) / $rafters_step * $lathing_step;
    $array["span_moment"] = 0.07*$array["Length_power"] * pow($rafters_step,2) + 0.207 * 120 * $rafters_step;
    $array["moment_Mx"] = $array["span_moment"]*cos($roof_pitch*M_PI/180);
    $array["moment_My"] = $array["span_moment"]*sin($roof_pitch*M_PI/180);
    $array["Wx"] = $lathing_b*pow($lathing_h,2)/6;
    $array["Wy"] = $lathing_h*pow($lathing_b,2)/6;
    $array["strain"] = ($lathing_step-$lathing_b*0.01) > 0.15
      ? ($array["moment_Mx"] * 100) / $array["Wx"] + ($array["moment_My"]*100) / $array["Wy"]
      : (($array["moment_Mx"] * 100) / $array["Wx"] + ($array["moment_My"]*100) / $array["Wy"])*0.5;

    return $array;
  }

?>



