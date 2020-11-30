<?php

return [
    /*'combo_boolean' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'gtsnotify_main',
    ],*/
    'frontend_js' => [
      'xtype' => 'textfield',
      'value' => '[[+jsUrl]]web/default.js',
      'area' => 'gtsnotify_main',
    ],
    'frontend_css' => [
      'xtype' => 'textfield',
      'value' => '[[+cssUrl]]web/default.css',
      'area' => 'gtsnotify_main',
    ],
    'gtsnotifyru_js' => [
      'xtype' => 'textfield',
      'value' => '[[+jsUrl]]web/gtsnotifyru.js',
      'area' => 'gtsnotify_main',
    ],
    'notify' => [
      'xtype' => 'textfield',
      'value' => '{
        "loadModels": "gtsNotify",
        "selects": {
          "Channel": {
            "type": "select",
            "class": "gtsNotifyChannel",
            "pdoTools": {
              "class": "gtsNotifyChannel",
              "select": "gtsNotifyChannel.id,gtsNotifyChannel.name",
              "sortby": {
                "gtsNotifyChannel.sort": "ASC"
              },
              "where": {
                "gtsNotifyChannel.active": 1
              }
            },
            "content": "{$name}"
          },
          "users": {
            "type": "select",
            "class": "modUser",
            "pdoTools": {
              "class": "modUser",
              "select": "modUser.id,modUser.username",
              "sortby": {
                "modUser.username": "ASC"
              }
            },
            "content": "{$username}"
          }
        },
        "tabs": {
          "Notify": {
            "label": "Уведомления",
            "table": {
              "class": "gtsNotifyNotify",
              "subtables": {
                "gtsNotifyNotifyPurpose": {
                  "class": "gtsNotifyNotifyPurpose",
                  "sub_where":{"notify_id":"id"},
                  "actions": {
                    "create": [],
                    "update": [],
                    "remove": []
                  },
                  "pdoTools": {
                    "class": "gtsNotifyNotifyPurpose"
                  },
                  "checkbox": 1,
                  "autosave": 1,
                  "row": {
                    "id": {
                      "cls": "",
                      "edit": {
                        "type": "hidden"
                      }
                    },
                    "notify_id": {
                      "label": "ID Уведомление",
                      "filter": 1
                    },
                    "user_id": {
                      "label": "Пользователь",
                      "filter": 1,
                      "edit": {
                        "type": "select",
                        "select": "users"
                      }
                    },
                    "channel_id": {
                      "label": "Канал",
                      "filter": 1,
                      "edit": {
                        "type": "select",
                        "select": "Channel"
                      }
                    },
                    "active": {
                      "label": "Активно",
                      "filter": 1,
                      "edit": {
                        "type": "checkbox"
                      },
                      "default": 1
                    }
                  }
                }
              },
              "actions": {
                "create": [],
                "update": [],
                "subtable": {
                  "subtable_name": "gtsNotifyNotifyPurpose"
                },
                "send_notify": {
                  "action": "gtsNotify/send_notify",
                  "icon": "glyphicon glyphicon-play",
                  "title": "Отправить уведомление",
                  "cls": "",
                  "row": []
                },
                "remove": []
              },
              "pdoTools": {
                "class": "gtsNotifyNotify"
              },
              "checkbox": 1,
              "autosave": 1,
              "row": {
                "id": {
                  "cls": "",
                  "edit": {
                    "type": "hidden"
                  }
                },
                "time": {
                  "label": "Время",
                  "filter": 1
                },
                "url": {
                  "label": "Ссылка",
                  "filter": 1
                },
                "json": {
                  "label": "Сообщение",
                  "edit": {
                    "type": "textarea",
                    "skip_sanitize": 0
                  }
                }
              }
            }
          },
          "WSClient": {
            "label": "Пользователи",
            "table": {
              "class": "gtsNotifyWSClient",
              "actions": {
                "remove": []
              },
              "pdoTools": {
                "class": "gtsNotifyWSClient"
              },
              "checkbox": 1,
              "row": {
                "id": {
                  "cls": "",
                  "edit": {
                    "type": "hidden"
                  }
                },
                "user_id": {
                  "label": "Пользователь",
                  "filter": 1,
                  "edit": {
                    "type": "select",
                    "select": "users"
                  }
                }
              }
            }
          }
        }
      }',
      'area' => 'gtsnotify_main',
      ],
      /*"compile": 1,
          "showLog": 1,
          "debug": 1,*/
    'setting' => [
        'xtype' => 'textfield',
        'value' => '{
          "loadModels": "gtsNotify",
          
          "selects": {
            "Channel": {
              "type": "select",
              "class": "gtsNotifyChannel",
              "pdoTools": {
                "class": "gtsNotifyChannel",
                "select": "gtsNotifyChannel.id,gtsNotifyChannel.name",
                "sortby": {
                  "gtsNotifyChannel.sort": "ASC"
                },
                "where":{
                  "gtsNotifyChannel.active":1
                }
              },
              "content": "{$name}"
            }
          },
          "tabs": {
            "Channels": {
              "label": "Каналы",
              "table": {
                "class": "gtsNotifyChannel",
                "actions": {
                  "create": [],
                  "update": [],
                  "remove": []
                },
                "pdoTools": {
                  "class": "gtsNotifyChannel"
                },
                "checkbox": 1,
                "autosave": 1,
                "row": {
                  "id": {
                    "cls": "",
                    "edit": {
                      "type": "hidden"
                    }
                  },
                  "name": {
                    "label":"Имя",
                    "filter": 1
                  },
                  "icon": {
                    "label":"Иконка"
                  },
                  "icon_empty": {
                    "label":"Иконка пустого канала"
                  },
                  
                  "tpl": {
                    "label":"Чанк"
                  },
                  "email_send": {
                    "label":"Отправлять по email",
                      "filter": 1,
                      "edit": {
                          "type": "checkbox"
                      },
                      "default":0
                  },
                  "email_tpl": {
                    "label":"Чанк email"
                  },
                  "email_sleep": {
                    "label":"Задержка email для того, чтобы юзерам онлайн не приходили письма"
                  },
                  "active": {
                    "label":"Активно",
                      "filter": 1,
                      "edit": {
                          "type": "checkbox"
                      },
                      "default":1
                  },
                  "hidden": {
                    "label":"Скрытый",
                      "filter": 1,
                      "edit": {
                          "type": "checkbox"
                      },
                      "default":0
                  },
                  "description": {
                    "label":"Описание",
                    "edit": {
                      "type": "textarea",
                      "skip_sanitize": 0
                    }
                  }
                }
              }
            },
            "Provider": {
              
              "label": "RealTime провайдер уведомлений",
              "table": {
                "class": "gtsNotifyProvider",
                "actions": {
                  "create": [],
                  "update": [],
                  "remove": []
                  
                },
                "pdoTools": {
                  "class": "gtsNotifyProvider"
                },
                "checkbox": 1,
                "autosave": 1,
                "row": {
                  "id": {
                    "cls": "",
                    "edit": {
                      "type": "hidden"
                    }
                  },
                  "name": {
                    "label":"Имя",
                    "filter": 1
                  },
                  "class": {
                    "label":"PHP класс",
                    "filter": 1
                  },
                  "path": {
                    "label":"Путь к PHP классу",
                    "filter": 1
                  },
                  "ws_address": {
                    "label":"Адрес сервера",
                    "filter": 1
                  },
                  "secret_key": {
                    "label":"secret_key",
                    "filter": 1
                  },
                  "host": {
                    "label":"Домен сайта",
                    "filter": 1
                  },
                  "description": {
                    "label":"Описание",
                    "edit": {
                      "type": "textarea",
                      "skip_sanitize": 0
                    }
                  },
                  "active": {
                    "label":"Активно",
                      "filter": 1,
                      "edit": {
                          "type": "checkbox"
                      },
                      "default":1
                  }
                }
              }
            }
          }
        }',
        'area' => 'gtsnotify_main',
    ],
];