   
        $('.js-search-ajax').select2({
            ajax: {
                url: "/search/client",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.items,
                        pagination: {
                            more: false//(params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            },

            placeholder: 'Rechercher un client (au moins 3 caractères)',
            minimumInputLength: 3,
            templateResult: formatRepo,
            templateSelection: formatRepoSelection
        });

        function formatRepo (repo) {
            if (repo.loading) {
                return repo.text;                
            }

            var $container = $(
            "<div class='select2-result- clearfix'>" +
                "<div class='select2-result-__meta'>" +
                    "<div class='select2-result__nomscode'></div>" +
                "</div>" +
            "</div>" 
        );

            $container.find(".select2-result__nomscode").text(repo.nom + " " + repo.prenom + " ("+repo.phone+")");
            return $container;
        }

        function formatRepoSelection (repo) {
            if(repo.id != ""){
                return repo.nom + ' '+ repo.prenom + ' ('+repo.phone+') '
            }else{
                return repo.text
            }
            
        }


