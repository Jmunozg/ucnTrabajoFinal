package com.jmunoz.login.retrofit;

import com.jmunoz.login.model.Login;
import com.jmunoz.login.model.LoginRequest;

import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.POST;

public interface ApiService {

    @POST("login/auth")
    Call<Login> getLogin(
            @Body LoginRequest body
    );

}
