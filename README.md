# OAuth

OAuth 服务器部署

## 1. 创建 Secret（如果有必要的话）

```bash
kubectl create namespace ecosystem

kubectl create --namespace ecosystem secret docker-registry latteart-registry    \
  --docker-server=registry.daisukide.com:2083 \
  --docker-username=<用户名> \
  --docker-password=<密码>
```
