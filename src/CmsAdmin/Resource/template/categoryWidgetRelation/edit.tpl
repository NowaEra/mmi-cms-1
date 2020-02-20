<div class="container-fluid">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <h5>{$category->name}</h5>
                <div class="card mt-4">
                    <div class="card-header">
                        <strong>{if $widget}{$widget->getName()} {/if}{#template.categoryWidgetRelation.config.header#}</strong>
                    </div>
                    <div class="card-body">
                        {$output}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>