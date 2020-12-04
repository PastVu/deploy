version: '3.8'
services:
  fileserver:
    networks:
      - traefik-public
    deploy:
      labels:
        # ENABLE TRAEFIK
        - traefik.enable=true
        - traefik.docker.network=traefik-public
        - traefik.constraint-label=traefik-public

        # MIDDLEWARE DEFINITIONS
        # REDIRECT WWW TO NON-WWW
        - traefik.http.middlewares.www-redirect.redirectregex.regex=^https?://w+\.${DOMAIN}/(.*)
        - traefik.http.middlewares.www-redirect.redirectregex.replacement=https://${DOMAIN}/$${1}
        - traefik.http.middlewares.www-redirect.redirectregex.permanent=true

        # WWW REDIRECT ROUTER
        - traefik.http.routers.pastvu-www-redirect.rule=Host(`w.${DOMAIN}`, `ww.${DOMAIN}`, `www.${DOMAIN}`)
        - traefik.http.routers.pastvu-www-redirect.middlewares=www-redirect
        - traefik.http.routers.pastvu-www-redirect.priority=5000

        # FILESERVER ROUTER
        - traefik.http.routers.pastvu-fileserver.rule=Host(`${DOMAIN}`)&&PathPrefix(`/download`, `/sitemap`, `/files`, `/_`)
        - traefik.http.routers.pastvu-fileserver.priority=2000

        # ENABLE LOADBALANCER
        - traefik.http.services.pastvu-fileserver.loadbalancer.server.port=80

# FRONTEND =============================================================================
<?php define("FRONTEND_RULE",'`${DOMAIN}`)&&PathPrefix(`/js`, `/img`, `/style`, `/tpl`)') ?>


  frontend_ru:
    networks:
      - traefik-public
    deploy:
      labels:
        # ENABLE TRAEFIK
        - traefik.enable=true
        - traefik.docker.network=traefik-public
        - traefik.constraint-label=traefik-public

        # ACCEPT-LANG FRONTEND_RU ROUTER
        - traefik.http.routers.pastvu-frontend-ru-acclang.rule=<?=FRONTEND_RULE?>&&HeadersRegexp(`Accept-Language`, `^ru.*`)
        - traefik.http.routers.pastvu-frontend-ru-acclang.priority=200

        # COOKIE FRONTEND_RU ROUTER
        - traefik.http.routers.pastvu-frontend-ru-cookie.rule=<?=FRONTEND_RULE?>&&HeadersRegexp(`Cookie`, `past.lang=ru`)
        - traefik.http.routers.pastvu-frontend-ru-cookie.priority=400

        # ROOT FRONTEND ROUTER
        - traefik.http.routers.pastvu-frontend-root.rule=Host(`${DOMAIN}`)&&Path(`/{file:.*}.{ext:(ico|png|txt)}`)
        - traefik.http.routers.pastvu-frontend-root.priority=50

        # ENABLE LOADBALANCER
        - traefik.http.services.pastvu-frontend-ru.loadbalancer.server.port=80

  frontend_en:
    networks:
      - traefik-public
    deploy:
      labels:
        # ENABLE TRAEFIK
        - traefik.enable=true
        - traefik.docker.network=traefik-public
        - traefik.constraint-label=traefik-public

        # DEFAULT FRONTEND_EN ROUTER
        - traefik.http.routers.pastvu-frontend-en-default.rule=<?=FRONTEND_RULE?>
        - traefik.http.routers.pastvu-frontend-en-default.priority=100

        # COOKIE FRONTEND_EN ROUTER
        - traefik.http.routers.pastvu-frontend-en-cookie.rule=<?=FRONTEND_RULE?>&&HeadersRegexp(`Cookie`, `past.lang=en`)
        - traefik.http.routers.pastvu-frontend-en-cookie.priority=300

        # ENABLE LOADBALANCER
        - traefik.http.services.pastvu-frontend-en.loadbalancer.server.port=80

  app_ru:
    networks:
      - traefik-public
    deploy:
      labels:
        # ENABLE TRAEFIK
        - traefik.enable=true
        - traefik.docker.network=traefik-public
        - traefik.constraint-label=traefik-public

        # ACCEPT-LANGUAGE APP_RU ROUTER
        - traefik.http.routers.pastvu-app-ru-acclang.rule=Host(`${DOMAIN}`)&&HeadersRegexp(`Accept-Language`, `^ru.*`)
        - traefik.http.routers.pastvu-app-ru-acclang.priority=20

        # COOKIE APP_RU ROUTER
        - traefik.http.routers.pastvu-app-ru-cookie.rule=Host(`${DOMAIN}`)&&HeadersRegexp(`Cookie`, `past.lang=ru`)
        - traefik.http.routers.pastvu-app-ru-cookie.priority=40

        # LOADBALANCER
        - traefik.http.services.pastvu-app-ru.loadbalancer.server.port=3000
        - traefik.http.services.pastvu-app-ru.loadbalancer.sticky.cookie=true
        - traefik.http.services.pastvu-app-ru.loadbalancer.sticky.cookie.name=app_ru

  app_en:
    networks:
      - traefik-public
    deploy:
      labels:
        # ENABLE TRAEFIK
        - traefik.enable=true
        - traefik.docker.network=traefik-public
        - traefik.constraint-label=traefik-public

        # DEFAULT APP_EN ROUTER
        - traefik.http.routers.pastvu-app-en-default.rule=Host(`${DOMAIN}`)
        - traefik.http.routers.pastvu-app-en-default.priority=10

        # COOKIE APP_EN ROUTER
        - traefik.http.routers.pastvu-app-en-cookie.rule=Host(`${DOMAIN}`)&&HeadersRegexp(`Cookie`, `past.lang=en`)
        - traefik.http.routers.pastvu-app-en-cookie.priority=30

        # LOADBALANCER
        - traefik.http.services.pastvu-app-en.loadbalancer.server.port=3000
        - traefik.http.services.pastvu-app-en.loadbalancer.sticky.cookie=true
        - traefik.http.services.pastvu-app-en.loadbalancer.sticky.cookie.name=app_en

  uploader:
    networks:
      - traefik-public
    deploy:
      labels:
        # ENABLE TRAEFIK
        - traefik.enable=true
        - traefik.docker.network=traefik-public
        - traefik.constraint-label=traefik-public

        - traefik.http.middlewares.upload-limit.buffering.maxRequestBodyBytes=52428800

        # HTTPS
        - traefik.http.routers.pastvu-uploader.rule=Host(`${DOMAIN}`)&&PathPrefix(`/upload`)
        - traefik.http.routers.pastvu-uploader.middlewares=upload-limit
        - traefik.http.routers.pastvu-uploader.priority=1000

        # ENABLE LOADBALANCER
        - traefik.http.services.pastvu-uploader.loadbalancer.server.port=3001


# API =============================================================================
#
  api:
    networks:
      - traefik-public
    deploy:
      labels:
        # ENABLE TRAEFIK
        - traefik.enable=true
        - traefik.docker.network=traefik-public
        - traefik.constraint-label=traefik-public

        # CORS MIDDLEWARE
        - traefik.http.middlewares.cors-headers.headers.accesscontrolalloworiginlist=*

        # API ROUTER
        - traefik.http.routers.pastvu-api.rule=Host(`${DOMAIN}`)&&PathPrefix(`/api2`)
        - traefik.http.routers.pastvu-api.middlewares=cors-headers
        - traefik.http.routers.pastvu-api.priority=3000

        # LOADBALANCER
        - traefik.http.services.pastvu-api.loadbalancer.server.port=3000
networks:
  traefik-public:
    external: true
