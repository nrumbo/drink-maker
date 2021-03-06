<?php namespace App\Http\Controllers;
use App\Drink;
use App\flasher;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: marcobellan
 * Date: 28/05/15
 * Time: 15:09
 */




class DrinkController extends Controller {

    /**
     * Adds a drink given ingredients and parameters
     * @param Request $r
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function add(Request $r){
        if(!$r->has('name')){
            flasher::error('Fill in the name');
            return redirect('admin#drinks');
        }
        $fileName=null;
        if($r->hasFile('photo')){
            $destinationPath = 'uploads';
            $extension = $r->file('photo')->getClientOriginalExtension(); // getting image extension
            $fileName = $r->input('name').'.'.$extension;
        }
        $ingredients=$r->input('ingredients');
        $parts=$r->input('parts');
        $recognizedIngredients=[];
        $recognizedParts=[];
        for($i=0;$i<5;$i++){
            if($ingredients[$i]!=0){
                if($i<4&&$ingredients[$i]==$ingredients[$i+1]){
                    $parts[$i+1]+=$parts[$i];
                    continue;
                }else{
                    array_push($recognizedIngredients,$ingredients[$i]);
                    array_push($recognizedParts,$parts[$i]);
                }
            }
        }
        if(count($recognizedIngredients)==0){
            flasher::error('Choose at least one ingredient');
            return redirect('admin#drinks');
        }
        $drink=Drink::create(['name'=>$r->input('name'),'volume'=>$r->input('volume'),'photo'=>$fileName]);
        for($i=0;$i<count($recognizedIngredients);$i++){
                $drink->Ingredients()->attach($recognizedIngredients[$i],['needed'=>$recognizedParts[$i]]);
        }
        if($fileName!=null)$r->file('photo')->move($destinationPath, $fileName);

        flasher::success('Drink added successfully');
        return redirect('admin#drinks');
    }


    /**
     * Deletes a drink with a specified id if it doesn't have any orders
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function delete($id){
        if(Drink::find($id)->Orders()->whereIn('status',[0,1,2])->count()!=0){
            flasher::error('There are some orders relative to this drink, delete them first');
            return redirect('admin#drinks');
        }
        Drink::find($id)->delete();
        flasher::success('Drink deleted correctly');
        return redirect('admin#drinks');
    }
}