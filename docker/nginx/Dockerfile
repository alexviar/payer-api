FROM nginx:alpine

# Copiar la configuración de Nginx
COPY ./docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]