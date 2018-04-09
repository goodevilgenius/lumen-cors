<?php namespace palanik\lumen\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class LumenCors {

    protected $settings = array(
                'origin' => '*',    // Wide Open!
                'allowMethods' => 'GET,HEAD,PUT,POST,DELETE,PATCH,OPTIONS',
                );

    /**
     * Set Access-Control-Allow-Origin on Response.
     *
     * @param  Request  $req
     * @return Response
     */
    protected function setOrigin($req, $rsp) {
        $origin = $this->settings['origin'];
        if (is_callable($origin)) {
            // Call origin callback with request origin
            $origin = call_user_func($origin,
                                    $req->header("Origin")
                                    );
        }
        $rsp->header('Access-Control-Allow-Origin', $origin);
    }

    /**
     * Set Access-Control-Expose-Headers on Response.
     *
     * @param  Request  $req
     * @return Response
     */
    protected function setExposeHeaders($req, $rsp) {
        if (isset($this->settings['exposeHeaders'])) {
            $exposeHeaders = $this->settings['exposeHeaders'];
            if (is_array($exposeHeaders)) {
                $exposeHeaders = implode(", ", $exposeHeaders);
            }
            
            $rsp->header('Access-Control-Expose-Headers', $exposeHeaders);
        }
    }

    /**
     * Set Access-Control-Max-Age on Response.
     *
     * @param  Request  $req
     * @return Response
     */
    protected function setMaxAge($req, $rsp) {
        if (isset($this->settings['maxAge'])) {
            $rsp->header('Access-Control-Max-Age', $this->settings['maxAge']);
        }
    }

    /**
     * Set Access-Control-Allow-Credentials on Response.
     *
     * @param  Request  $req
     * @return Response
     */
    protected function setAllowCredentials($req, $rsp) {
        if (isset($this->settings['allowCredentials']) && $this->settings['allowCredentials'] === True) {
            $rsp->header('Access-Control-Allow-Credentials', 'true');
        }
    }

    /**
     * Set Access-Control-Allow-Methods on Response.
     *
     * @param  Request  $req
     * @return Response
     */
    protected function setAllowMethods($req, $rsp) {
        if (isset($this->settings['allowMethods'])) {
            $allowMethods = $this->settings['allowMethods'];
            if (is_array($allowMethods)) {
                $allowMethods = implode(", ", $allowMethods);
            }
            
            $rsp->header('Access-Control-Allow-Methods', $allowMethods);
        }
    }

    /**
     * Set Access-Control-Allow-Headers on Response.
     *
     * @param  Request  $req
     * @return Response
     */
    protected function setAllowHeaders($req, $rsp) {
        if (isset($this->settings['allowHeaders'])) {
            $allowHeaders = $this->settings['allowHeaders'];
            if (is_array($allowHeaders)) {
                $allowHeaders = implode(", ", $allowHeaders);
            }
        }
        else {  // Otherwise, use request headers
            $allowHeaders = $req->header("Access-Control-Request-Headers");
        }

        if (isset($allowHeaders)) {
            $rsp->header('Access-Control-Allow-Headers', $allowHeaders);
        }
    }

    /**
     * Set all needed Cors Headers on Response.
     *
     * @param  Request  $req
     * @return Response
     */
    protected function setCorsHeaders($req, $rsp) {

        // http://www.html5rocks.com/static/images/cors_server_flowchart.png
        // Pre-flight
        if ($req->isMethod('OPTIONS')) {
            $this->setOrigin($req, $rsp);
            $this->setMaxAge($req, $rsp);
            $this->setAllowCredentials($req, $rsp);
            $this->setAllowMethods($req, $rsp);
            $this->setAllowHeaders($req, $rsp);
        }
        else {
            $this->setOrigin($req, $rsp);
            $this->setExposeHeaders($req, $rsp);
            $this->setAllowCredentials($req, $rsp);
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        if ($request->isMethod('OPTIONS')) {
            $response = new Response("", 200);
        }
        else {
            $response = $next($request);
        }

        $this->setCorsHeaders($request, $response);

        return $response;
    }

}
