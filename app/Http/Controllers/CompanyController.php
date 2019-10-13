<?php

namespace App\Http\Controllers;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $CompanyValue = Company::select('id','name','address','phone','corporate_email')              
        ->first();      

        return response()
        ->json([
        'form' =>  collect($CompanyValue)->isEmpty() ? Company::initialize() : $CompanyValue
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Company  $companyId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {                
        $this->validate($request, [
            'name' => 'required'
        ]);       
        
        $newCompanyValues = $request->all();         
       
        if (collect($newCompanyValues)->isEmpty()) {
            return response()
            ->json([
                'custom_message' => 'Debe ingresar por lo menos un valor en el formulario'
            ], 422);
        };

        $checkIfExistData = Company::first();    
        
        if (collect($checkIfExistData)->isEmpty())
        {      
            $item = Company::create($newCompanyValues);
        }
        else
        {
            $item = Company::findOrFail($checkIfExistData->id);
            $item->update($newCompanyValues);
        }
        
        return response()
        ->json([
            'updated' => true,
            'id' => $item->id             
        ]);
    }

   
}
