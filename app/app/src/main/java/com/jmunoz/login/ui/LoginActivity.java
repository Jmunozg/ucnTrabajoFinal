package com.jmunoz.login.ui;

import androidx.appcompat.app.AppCompatActivity;

import android.app.ProgressDialog;
import android.content.Intent;
import android.os.Bundle;
import android.text.TextUtils;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.jmunoz.login.R;
import com.jmunoz.login.model.Login;
import com.jmunoz.login.model.LoginRequest;
import com.jmunoz.login.retrofit.ApiAdapter;
import com.jmunoz.login.retrofit.ApiService;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class LoginActivity extends AppCompatActivity implements View.OnClickListener {

    private static final String NombreUsuario = "NombreUsuario";

    // UI references.
    private EditText mEmailView, mPassword;
    private ProgressDialog pd = null;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        mEmailView = (EditText) findViewById(R.id.username);
        mPassword = (EditText) findViewById(R.id.password);

        Button mEmailSignInButton = (Button) findViewById(R.id.login);
        mEmailSignInButton.setOnClickListener(this);
    }

    private void attemptLogin() {
        // Reset errors.
        mEmailView.setError(null);
        mPassword.setError(null);

        // Store values at the time of the login attempt.
        final String email = mEmailView.getText().toString();
        final String password = mPassword.getText().toString();

        boolean cancel = false;
        View focusView = null;

        if (TextUtils.isEmpty(email)) {
            mEmailView.setError(getString(R.string.error_field_required));
            focusView = mEmailView;
            cancel = true;
        }

        if (TextUtils.isEmpty(password)) {
            mPassword.setError(getString(R.string.error_field_required));
            focusView = mPassword;
            cancel = true;
        }

        if (cancel) {
            // There was an error; don't attempt login and focus the first
            // form field with an error.
            focusView.requestFocus();
        }
        else {
            // Show a progress spinner, and kick off a background task to
            // perform the user login attempt.
            showProgress(true);

            LoginRequest loginRequest = new LoginRequest();
            loginRequest.Username = email;
            loginRequest.Password = password;

            ApiService service = ApiAdapter.getApiService();
            Call<Login> requesCatalogo = service.getLogin(loginRequest);

            requesCatalogo.enqueue(new Callback<Login>() {
                @Override
                public void onResponse(Call<Login> call, Response<Login> response) {
                    try {
                        if (!response.isSuccessful()) {
                            Toast.makeText(getApplicationContext(), "Favor verificar la conexión.", Toast.LENGTH_LONG).show();
                            Log.i("TAG", "Error" + response.code());
                            mEmailView.setError(getString(R.string.error_field_required));
                            mEmailView.requestFocus();
                        } else {
                            Login login = response.body();

                            if (!login.respuesta) {
                                Toast.makeText(getApplicationContext(), login.mensaje, Toast.LENGTH_LONG).show();
                                showProgress(false);
                                return;
                            }

                            Log.i("TAG", String.format("%s: %s", login.usuario.username, login.usuario.Token));
                            Intent mainIntent = new Intent().setClass(
                                    LoginActivity.this, MainActivity.class);

                            Bundle paramentros = new Bundle();
                            paramentros.putString(NombreUsuario, login.usuario.Nombre);
                            mainIntent.putExtras(paramentros);
                            startActivity(mainIntent);
                            finish();
                        }

                        showProgress(false);
                    }
                    catch (Exception ex){
                        Toast.makeText(getApplicationContext(), "Se presento una inconsistencia, por favor contactar al administrador.", Toast.LENGTH_LONG).show();
                        showProgress(false);
                    }
                }

                @Override
                public void onFailure(Call<Login> call, Throwable t) {
                    Log.e("TAG", "Error: " + t.getMessage());

                    if (t.getMessage().contains("failed to connect")) {
                        Toast.makeText(getApplicationContext(), "No se encuentra conectado al servidor. Favor revisar su conexión.", Toast.LENGTH_LONG).show();
                    } else {
                        Toast.makeText(getApplicationContext(), "No se pudo realizar el inicio de sesión.", Toast.LENGTH_LONG).show();
                    }
                    showProgress(false);
                }
            });
        }
    }

    /**
     * Shows the progress UI and hides the login form.
     */
    private void showProgress(final boolean show) {
        if(!show){
            if (pd != null && pd.isShowing()) {
                pd.dismiss();
                pd = null;
            }
        } else {
            if(pd == null){
                pd = ProgressDialog.show(this, "Conectando con el servidor", "Espere un momento por favor...", true);
            }
        }
    }

    @Override
    public void onClick(View v) {
        attemptLogin();
    }


}